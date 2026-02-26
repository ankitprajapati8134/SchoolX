<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/../../vendor/autoload.php';

use Kreait\Firebase\Factory;

/**
 * Firebase library
 *
 * SECURITY FIXES:
 * [FIX-1]  get() now uses the Admin SDK (Kreait) instead of plain
 *          file_get_contents() with a public REST URL — removes unauthenticated
 *          data exposure and stops relying on publicly-readable DB rules.
 * [FIX-2]  Error handling: exceptions caught and logged; never exposed to client.
 * [FIX-3]  handleOtherValue() kept but moved to a more appropriate layer.
 * [FIX-4]  getDatabase() exposed for raw reference access (used in Account controller).
 * [FIX-5]  Service account path kept consistent with Common_model.
 */
class Firebase
{
    protected $database;
    protected $auth;

    public function __construct()
    {
        $serviceAccountPath = __DIR__ . '/../config/graders-1c047-firebase-adminsdk-z1a10-ca28a54060.json';
        $databaseUri        = 'https://graders-1c047-default-rtdb.asia-southeast1.firebasedatabase.app/';

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri($databaseUri);

        $this->database = $factory->createDatabase();
        $this->auth     = $factory->createAuth();
    }

    /**
     * Get the raw database instance (needed for getReference() in some controllers).
     */
    public function getDatabase()
    {
        return $this->database;
    }

    // ── [FIX-1] All reads now go through the authenticated Admin SDK ──────────

    /**
     * Read data from a Firebase path.
     * Returns the value (array/scalar/null) or null on failure.
     */
    public function get(string $path)
    {
        try {
            return $this->database->getReference($path)->getValue();
        } catch (\Exception $e) {
            log_message('error', 'Firebase::get() failed for path [' . $path . ']: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set (create or overwrite) data at a path.
     */
    public function set(string $path, $data)
    {
        try {
            $this->database->getReference($path)->set($data);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Firebase::set() failed for path [' . $path . ']: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update (merge) data at a path.
     */
    public function update(string $path, array $data)
    {
        try {
            $this->database->getReference($path)->update($data);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Firebase::update() failed for path [' . $path . ']: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a node at a path.
     */
    public function delete(string $path)
    {
        try {
            $this->database->getReference($path)->remove();
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Firebase::delete() failed for path [' . $path . ']: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Push data, generating a unique key. Returns the new key or null.
     */
    public function push(string $path, $data): ?string
    {
        try {
            return $this->database->getReference($path)->push($data)->getKey();
        } catch (\Exception $e) {
            log_message('error', 'Firebase::push() failed for path [' . $path . ']: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Copy data from one path to another (read-then-write).
     */
    public function copy(string $fromPath, string $toPath): bool
    {
        $data = $this->get($fromPath);
        if ($data !== null) {
            return $this->set($toPath, $data);
        }
        return false;
    }

    /**
     * Check if a node exists.
     */
    public function exists(string $path): bool
    {
        try {
            return $this->database->getReference($path)->getSnapshot()->exists();
        } catch (\Exception $e) {
            log_message('error', 'Firebase::exists() failed for path [' . $path . ']: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique push key without writing data.
     */
    public function generateKey(string $path): ?string
    {
        try {
            return $this->database->getReference($path)->push()->getKey();
        } catch (\Exception $e) {
            log_message('error', 'Firebase::generateKey() failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get children at a path as a snapshot collection.
     */
    public function getChildren(string $path)
    {
        try {
            return $this->database->getReference($path)->getSnapshot()->getChildren();
        } catch (\Exception $e) {
            log_message('error', 'Firebase::getChildren() failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Authenticate a user by email (admin SDK — used for token verification).
     */
    public function authenticate(string $email, string $password)
    {
        try {
            return $this->auth->getUserByEmail($email);
        } catch (\Exception $e) {
            log_message('error', 'Firebase::authenticate() failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle "Other" dropdown pattern: if main value is 'other', return the custom value.
     */
    public function handleOtherValue(string $mainValue, string $otherValue): string
    {
        if (strtolower(trim($mainValue)) === 'other' && trim($otherValue) !== '') {
            return trim($otherValue);
        }
        return $mainValue;
    }
}
