<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/../../vendor/autoload.php'; // Adjust the path according to your directory structure

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class Firebase
{
    private $BASE_URL;
    private $serviceAccountPath;
    protected $database;
    protected $auth;

    public function __construct()
    {
        $this->BASE_URL = 'https://graders-1c047-default-rtdb.asia-southeast1.firebasedatabase.app'; // Set your Firebase database URL here

        $this->serviceAccountPath = __DIR__ . '/../config/graders-1c047-firebase-adminsdk-z1a10-ca28a54060.json'; // Your JSON file path
        $databaseUri = 'https://graders-1c047-default-rtdb.asia-southeast1.firebasedatabase.app/'; // Your Firebase database URI

        $firebase = (new Factory)
            ->withServiceAccount($this->serviceAccountPath)
            ->withDatabaseUri($databaseUri);

        $this->database = $firebase->createDatabase();
        $this->auth = $firebase->createAuth();
    }

    // Function to GET data from a specified path
    // public function get($path)
    // {
    //     $url = $this->BASE_URL . '/' . $path . '.json'; // Adjust the URL as needed
    //     $response = file_get_contents($url); // Fetch data from Firebase
    //     return json_decode($response, true); // Decode JSON response to an array
    // }
    public function getDatabase()
    {
        return $this->database;
    }


    public function get($path)
    {
        // URL encode only the parts of the path that could contain special characters (school name)
        $encodedPath = str_replace([' ', ','], ['%20', '%2C'], $path);
        $url = $this->BASE_URL . '/' . $encodedPath . '.json'; // Correctly encoded URL

        // Debugging: Output the URL to ensure it's correct
        // echo "Request URL: $url"; // Uncomment for debugging

        // Fetch data from Firebase
        $response = @file_get_contents($url); // Use @ to suppress PHP warnings in case of failure

        // Check if the response is false (indicating an error)
        // if ($response === false) {
        //     $error = error_get_last(); // Get the last error message
        //     return 'Error fetching data: ' . $error['message'];
        // }
        if ($response === false) {
            // Log the error for debugging
            $error = error_get_last();
            log_message('error', 'Firebase connection failed: ' . $error['message']);
            return null; // Indicate failure
        }

        // Decode JSON response to an array
        return json_decode($response, true);
    }

    // public function get($path) {
    //     // URL encode the path to handle special characters
    //     $encodedPath = urlencode($path);
    //     $url = $this->BASE_URL . '/' .$encodedPath . '.json';

    //     // Use file_get_contents to fetch the data
    //     $response = file_get_contents($url);

    //     // Check if the response is false (indicating an error)
    //     if ($response === false) {
    //         return false; // Indicate failure
    //     }

    //     // Decode the JSON response into an associative array
    //     return json_decode($response, true);
    // }

    // Optional: Add error handling method to retrieve last error
    public function getLastError()
    {
        return error_get_last()['message'] ?? 'No error information available';
    }

    // Function to SET (create or replace) data at a specified path
    public function set($path, $data)
    {
        return $this->database->getReference($path)->set($data);
    }

    // Function to UPDATE data at a specified path (merge with existing data)
    public function update($path, $data)
    {
        return $this->database->getReference($path)->update($data);
    }

    // Function to DELETE data from a specified path
    public function delete($path)
    {
        return $this->database->getReference($path)->remove();
    }

    // Function to PUSH (add) data at a path, generating a unique key
    public function push($path, $data)
    {
        return $this->database->getReference($path)->push($data)->getKey(); // Return the unique key created
    }

    // Function to COPY data from one path to another
    public function copy($fromPath, $toPath)
    {
        $data = $this->get($fromPath); // Get data from the source path
        if ($data) {
            $this->set($toPath, $data); // Set data at the destination path
        }
    }

    // Function to check if a node exists
    public function exists($path)
    {
        return $this->database->getReference($path)->getSnapshot()->exists();
    }

    // Function to retrieve a list of children at a path
    public function getChildren($path)
    {
        return $this->database->getReference($path)->getSnapshot()->getChildren();
    }

    // Function to get a unique key (for generating IDs)
    public function generateKey($path)
    {
        return $this->database->getReference($path)->push()->getKey();
    }

    // Function to authenticate a user (can be expanded)
    public function authenticate($email, $password)
    {
        try {
            $user = $this->auth->getUserByEmail($email);
            // Perform additional authentication logic as needed
            return $user;
        } catch (Exception $e) {
            return null; // User not found
        }
    }
    public function uploadFile($localFilePath, $storagePath)
    {
        try {
            // Ensure the file exists before attempting to open it
            if (!file_exists($localFilePath)) {
                return 'Error: File does not exist at ' . $localFilePath;
            }

            // Attempt to open the file
            $fileStream = fopen($localFilePath, 'r');
            if (!$fileStream) {
                return 'Error: Unable to open file for reading.';
            }

            $storage = (new Factory)
                ->withServiceAccount($this->serviceAccountPath)
                ->createStorage();

            $bucket = $storage->getBucket();
            $bucket->upload($fileStream, ['name' => $storagePath]);

            // Close file stream only if it was successfully opened
            if (is_resource($fileStream)) {
                fclose($fileStream);
            }

            return true;
        } catch (Exception $e) {
            return 'Firebase Storage Error: ' . $e->getMessage();
        }
    }

    public function getDownloadUrl($storagePath)
    {
        try {
            $storage = (new Factory)
                ->withServiceAccount($this->serviceAccountPath)
                ->createStorage();

            $bucket = $storage->getBucket();
            return "https://firebasestorage.googleapis.com/v0/b/" . $bucket->name() . "/o/" . urlencode($storagePath) . "?alt=media";
        } catch (Exception $e) {
            return 'Firebase Storage Error: ' . $e->getMessage();
        }
    }
    public function deleteStorageFile($filePath)
    {
        $storageBucket = "your-project-id.appspot.com"; // Change to your Firebase Storage Bucket
        $url = "https://firebasestorage.googleapis.com/v0/b/$storageBucket/o/" . urlencode($filePath);

        $headers = [
            "Authorization: Bearer " . $this->getAccessToken(), // Ensure you have a valid access token
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200; // Return true if deletion was successful
    }
    public function getAccessToken()
    {
        $keyFilePath = APPPATH . 'config/firebase_service_account.json'; // Ensure the correct path
        if (!file_exists($keyFilePath)) {
            throw new Exception("Firebase service account JSON file not found at $keyFilePath");
        }

        $keyFile = json_decode(file_get_contents($keyFilePath), true);
        $now = time();

        // Prepare JWT header
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        // Prepare JWT claim set
        $claims = [
            'iss' => $keyFile['client_email'],
            'scope' => 'https://www.googleapis.com/auth/devstorage.full_control',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        // Encode header and claims
        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlClaims = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=');
        $signatureInput = $base64UrlHeader . '.' . $base64UrlClaims;

        // Sign JWT using private key
        $privateKey = openssl_pkey_get_private($keyFile['private_key']);
        openssl_sign($signatureInput, $signature, $privateKey, 'SHA256');
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        // Construct JWT token
        $jwt = $signatureInput . '.' . $base64UrlSignature;

        // Request an access token
        $response = $this->makeHttpRequest('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        return $response['access_token'] ?? null;
    }
    private function makeHttpRequest($url, $postFields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function handleOtherValue($mainValue, $otherValue)
    {
        // Normalize input values to remove unwanted spaces and convert to lowercase for comparison.
        $normalizedMain = trim(strtolower($mainValue));
        $normalizedOther = trim($otherValue);

        // If the main value indicates 'Other', then return the alternative value (if provided),
        // otherwise return the main value itself.
        if ($normalizedMain === 'other' && !empty($normalizedOther)) {
            return $normalizedOther;
        }

        return $mainValue;
    }

}
