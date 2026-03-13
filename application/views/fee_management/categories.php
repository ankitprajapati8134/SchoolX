<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <div class="fm-wrap">
        <!-- Top bar -->
        <div class="fm-topbar">
            <h1 class="fm-page-title">
                <i class="fa fa-th-large"></i> Fee Categories
            </h1>
            <ul class="fm-breadcrumb">
                <li><a href="<?= base_url() ?>">Dashboard</a></li>
                <li>Fees &amp; Finance</li>
                <li>Categories</li>
            </ul>
        </div>

        <!-- Stats row -->
        <div class="fm-stats" id="fmCatStats">
            <div class="fm-stat">
                <div class="fm-stat-label">Total Categories</div>
                <div class="fm-stat-value" id="statTotal">--</div>
            </div>
            <div class="fm-stat teal">
                <div class="fm-stat-label">Academic</div>
                <div class="fm-stat-value" id="statAcademic">--</div>
            </div>
            <div class="fm-stat gold">
                <div class="fm-stat-label">Transport</div>
                <div class="fm-stat-value" id="statTransport">--</div>
            </div>
            <div class="fm-stat green">
                <div class="fm-stat-label">Extra-curricular</div>
                <div class="fm-stat-value" id="statExtra">--</div>
            </div>
            <div class="fm-stat">
                <div class="fm-stat-label">Other</div>
                <div class="fm-stat-value" id="statOther">--</div>
            </div>
        </div>

        <!-- Add / Edit Category Card -->
        <div class="fm-card">
            <div class="fm-card-head">
                <i class="fa fa-plus-circle" id="fmFormIcon"></i>
                <h3 id="fmFormTitle">Add Category</h3>
            </div>
            <div class="fm-card-body">
                <form id="categoryForm" autocomplete="off">
                    <input type="hidden" name="category_id" id="category_id" value="">
                    <div class="fm-form-grid">
                        <div class="fm-form-col">
                            <label class="fm-label">Category Name <span class="fm-req">*</span></label>
                            <input type="text" name="category_name" id="category_name"
                                class="fm-input" placeholder="e.g. Tuition Fees" required>
                        </div>
                        <div class="fm-form-col">
                            <label class="fm-label">Category Type <span class="fm-req">*</span></label>
                            <select name="category_type" id="category_type" class="fm-select" required>
                                <option value="">Select Type</option>
                                <option value="Academic">Academic</option>
                                <option value="Transport">Transport</option>
                                <option value="Extra-curricular">Extra-curricular</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="fm-form-col">
                            <label class="fm-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order"
                                class="fm-input" placeholder="0" min="0" value="0">
                        </div>
                        <div class="fm-form-col fm-form-col-full">
                            <label class="fm-label">Description <span class="fm-opt">(optional)</span></label>
                            <textarea name="description" id="category_desc"
                                class="fm-input fm-textarea" placeholder="Brief description of this category..." rows="2"></textarea>
                        </div>
                        <div class="fm-form-col fm-form-col-full">
                            <label class="fm-label">Fee Titles</label>
                            <div class="fm-checkbox-group" id="feeTitlesGroup">
                                <?php if (!empty($feesStructure)): ?>
                                    <?php if (!empty($feesStructure['Monthly'])): ?>
                                        <div class="fm-checkbox-section">
                                            <div class="fm-checkbox-section-head">
                                                <i class="fa fa-calendar"></i> Monthly
                                            </div>
                                            <?php foreach ($feesStructure['Monthly'] as $title => $val): ?>
                                                <label class="fm-check-label">
                                                    <input type="checkbox" name="fee_titles[]"
                                                        value="<?= htmlspecialchars($title) ?>"
                                                        class="fm-check-input">
                                                    <span class="fm-check-box"></span>
                                                    <span class="fm-check-text"><?= htmlspecialchars($title) ?></span>
                                                    <span class="fm-badge fm-badge-teal fm-badge-xs">Monthly</span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($feesStructure['Yearly'])): ?>
                                        <div class="fm-checkbox-section">
                                            <div class="fm-checkbox-section-head">
                                                <i class="fa fa-star"></i> Yearly
                                            </div>
                                            <?php foreach ($feesStructure['Yearly'] as $title => $val): ?>
                                                <label class="fm-check-label">
                                                    <input type="checkbox" name="fee_titles[]"
                                                        value="<?= htmlspecialchars($title) ?>"
                                                        class="fm-check-input">
                                                    <span class="fm-check-box"></span>
                                                    <span class="fm-check-text"><?= htmlspecialchars($title) ?></span>
                                                    <span class="fm-badge fm-badge-gold fm-badge-xs">Yearly</span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="fm-empty-sm">
                                        <i class="fa fa-info-circle"></i> No fee titles found. Add titles in Fees Structure first.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="fm-form-actions">
                        <button type="submit" class="fm-btn fm-btn-primary" id="btnSave">
                            <i class="fa fa-save"></i> <span id="btnSaveText">Save Category</span>
                        </button>
                        <button type="button" class="fm-btn fm-btn-outline" id="btnCancel" style="display:none">
                            <i class="fa fa-times"></i> Cancel Edit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories Table Card -->
        <div class="fm-card">
            <div class="fm-card-head">
                <i class="fa fa-list"></i>
                <h3>Categories List</h3>
            </div>
            <div class="fm-card-body" style="padding:0">
                <div class="fm-table-wrap">
                    <table class="fm-table" id="categoriesTable">
                        <thead>
                            <tr>
                                <th style="width:54px">S.No</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Fee Titles</th>
                                <th style="width:70px">Sort</th>
                                <th style="width:80px">Status</th>
                                <th style="width:100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesBody">
                            <tr>
                                <td colspan="7">
                                    <div class="fm-loading">
                                        <i class="fa fa-spinner fa-spin"></i> Loading categories...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="fm-toast-wrap" id="fmToastWrap"></div>

<!-- Confirm modal -->
<div class="fm-modal-overlay" id="fmModalOverlay">
    <div class="fm-modal">
        <div class="fm-modal-head">
            <i class="fa fa-exclamation-triangle"></i>
            <span>Confirm Delete</span>
        </div>
        <div class="fm-modal-body" id="fmModalMsg">Are you sure you want to delete this category?</div>
        <div class="fm-modal-foot">
            <button class="fm-btn fm-btn-outline fm-btn-sm" id="fmModalCancel">Cancel</button>
            <button class="fm-btn fm-btn-danger fm-btn-sm" id="fmModalConfirm">
                <i class="fa fa-trash-o"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Config ── */
    var BASE = '<?= base_url() ?>';
    var CSRF_NAME = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
    var CSRF_HASH = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var categoriesData = [];
    var editingId = null;
    var pendingDeleteId = null;

    /* ── Init ── */
    loadCategories();

    /* ── Toast ── */
    function showToast(msg, type) {
        var wrap = document.getElementById('fmToastWrap');
        var el = document.createElement('div');
        el.className = 'fm-toast ' + (type || 'success');
        el.textContent = msg;
        wrap.appendChild(el);
        setTimeout(function () {
            el.style.transition = 'opacity .3s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 350);
        }, 3200);
    }

    /* ── Refresh CSRF from response ── */
    function refreshCsrf(data) {
        if (data && data.csrf_token) {
            CSRF_HASH = data.csrf_token;
        }
    }

    /* ── Build POST body ── */
    function buildFormData(obj) {
        var fd = new FormData();
        fd.append(CSRF_NAME, CSRF_HASH);
        for (var key in obj) {
            if (!obj.hasOwnProperty(key)) continue;
            if (Array.isArray(obj[key])) {
                obj[key].forEach(function (v) { fd.append(key + '[]', v); });
            } else {
                fd.append(key, obj[key]);
            }
        }
        return fd;
    }

    /* ── Fetch helper ── */
    function ajax(method, url, body) {
        var opts = {
            method: method,
            headers: {
                'X-CSRF-Token': CSRF_HASH,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        if (body) opts.body = body;
        return fetch(url, opts).then(function (res) { return res.json(); });
    }

    /* ── Load Categories ── */
    function loadCategories() {
        var tbody = document.getElementById('categoriesBody');
        tbody.innerHTML = '<tr><td colspan="7"><div class="fm-loading"><i class="fa fa-spinner fa-spin"></i> Loading categories...</div></td></tr>';

        ajax('GET', BASE + 'fee_management/fetch_categories')
            .then(function (r) {
                refreshCsrf(r);
                if (r.status === 'success' && r.categories) {
                    categoriesData = r.categories;
                    renderTable();
                    updateStats();
                } else {
                    categoriesData = [];
                    renderTable();
                    updateStats();
                }
            })
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="7"><div class="fm-empty"><i class="fa fa-exclamation-circle"></i> Failed to load categories.</div></td></tr>';
            });
    }

    /* ── Render Table ── */
    function renderTable() {
        var tbody = document.getElementById('categoriesBody');
        if (!categoriesData || categoriesData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7"><div class="fm-empty"><i class="fa fa-inbox"></i> No categories found. Add one above to get started.</div></td></tr>';
            return;
        }

        var html = '';
        categoriesData.forEach(function (cat, idx) {
            var typeBadgeClass = getTypeBadgeClass(cat.category_type);
            var titlesHtml = '';
            if (cat.fee_titles && cat.fee_titles.length > 0) {
                cat.fee_titles.forEach(function (t) {
                    titlesHtml += '<span class="fm-badge fm-badge-teal fm-badge-xs">' + escHtml(t) + '</span> ';
                });
            } else {
                titlesHtml = '<span class="fm-muted-text">None</span>';
            }
            var statusBadge = cat.status === 'inactive'
                ? '<span class="fm-badge fm-badge-red">Inactive</span>'
                : '<span class="fm-badge fm-badge-green">Active</span>';

            html += '<tr data-id="' + escAttr(cat.id) + '">'
                + '<td class="fm-cell-muted">' + (idx + 1) + '</td>'
                + '<td class="fm-cell-name">' + escHtml(cat.category_name) + '</td>'
                + '<td><span class="fm-badge ' + typeBadgeClass + '">' + escHtml(cat.category_type) + '</span></td>'
                + '<td class="fm-cell-titles">' + titlesHtml + '</td>'
                + '<td class="fm-cell-muted">' + (cat.sort_order || 0) + '</td>'
                + '<td>' + statusBadge + '</td>'
                + '<td class="fm-cell-actions">'
                    + '<button class="fm-btn fm-btn-teal fm-btn-xs" onclick="window._fmEditCat(\'' + escAttr(cat.id) + '\')" title="Edit"><i class="fa fa-pencil"></i></button> '
                    + '<button class="fm-btn fm-btn-danger fm-btn-xs" onclick="window._fmDeleteCat(\'' + escAttr(cat.id) + '\')" title="Delete"><i class="fa fa-trash-o"></i></button>'
                + '</td>'
                + '</tr>';
        });
        tbody.innerHTML = html;
    }

    /* ── Update Stats ── */
    function updateStats() {
        var counts = { total: 0, Academic: 0, Transport: 0, 'Extra-curricular': 0, Other: 0 };
        if (categoriesData) {
            counts.total = categoriesData.length;
            categoriesData.forEach(function (c) {
                if (counts.hasOwnProperty(c.category_type)) counts[c.category_type]++;
            });
        }
        document.getElementById('statTotal').textContent = counts.total;
        document.getElementById('statAcademic').textContent = counts.Academic;
        document.getElementById('statTransport').textContent = counts.Transport;
        document.getElementById('statExtra').textContent = counts['Extra-curricular'];
        document.getElementById('statOther').textContent = counts.Other;
    }

    /* ── Save Category (Add / Update) ── */
    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();

        var name = document.getElementById('category_name').value.trim();
        var type = document.getElementById('category_type').value;
        if (!name || !type) {
            showToast('Please fill in all required fields.', 'error');
            return;
        }

        var checked = [];
        var boxes = document.querySelectorAll('#feeTitlesGroup input[type="checkbox"]:checked');
        boxes.forEach(function (cb) { checked.push(cb.value); });

        var payload = {
            category_name: name,
            category_type: type,
            description: document.getElementById('category_desc').value.trim(),
            sort_order: document.getElementById('sort_order').value || '0',
            fee_titles: checked
        };

        var catId = document.getElementById('category_id').value;
        if (catId) payload.category_id = catId;

        var btn = document.getElementById('btnSave');
        var btnText = document.getElementById('btnSaveText');
        btn.disabled = true;
        btnText.textContent = 'Saving...';

        ajax('POST', BASE + 'fee_management/save_category', buildFormData(payload))
            .then(function (r) {
                refreshCsrf(r);
                btn.disabled = false;
                btnText.textContent = catId ? 'Update Category' : 'Save Category';
                if (r.status === 'success') {
                    showToast(r.message || 'Category saved successfully!', 'success');
                    resetForm();
                    loadCategories();
                } else {
                    showToast(r.message || 'Failed to save category.', 'error');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btnText.textContent = catId ? 'Update Category' : 'Save Category';
                showToast('Server error. Please try again.', 'error');
            });
    });

    /* ── Edit Category ── */
    window._fmEditCat = function (id) {
        var cat = null;
        for (var i = 0; i < categoriesData.length; i++) {
            if (categoriesData[i].id === id) { cat = categoriesData[i]; break; }
        }
        if (!cat) return;

        editingId = id;
        document.getElementById('category_id').value = id;
        document.getElementById('category_name').value = cat.category_name || '';
        document.getElementById('category_type').value = cat.category_type || '';
        document.getElementById('category_desc').value = cat.description || '';
        document.getElementById('sort_order').value = cat.sort_order || 0;

        // Set checkboxes
        var boxes = document.querySelectorAll('#feeTitlesGroup input[type="checkbox"]');
        var titles = cat.fee_titles || [];
        boxes.forEach(function (cb) {
            cb.checked = titles.indexOf(cb.value) !== -1;
        });

        document.getElementById('fmFormTitle').textContent = 'Edit Category';
        document.getElementById('fmFormIcon').className = 'fa fa-pencil';
        document.getElementById('btnSaveText').textContent = 'Update Category';
        document.getElementById('btnCancel').style.display = '';

        // Scroll to form
        document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    /* ── Cancel Edit ── */
    document.getElementById('btnCancel').addEventListener('click', function () {
        resetForm();
    });

    /* ── Reset Form ── */
    function resetForm() {
        editingId = null;
        document.getElementById('categoryForm').reset();
        document.getElementById('category_id').value = '';
        document.getElementById('sort_order').value = '0';
        document.getElementById('fmFormTitle').textContent = 'Add Category';
        document.getElementById('fmFormIcon').className = 'fa fa-plus-circle';
        document.getElementById('btnSaveText').textContent = 'Save Category';
        document.getElementById('btnCancel').style.display = 'none';

        var boxes = document.querySelectorAll('#feeTitlesGroup input[type="checkbox"]');
        boxes.forEach(function (cb) { cb.checked = false; });
    }

    /* ── Delete Category ── */
    window._fmDeleteCat = function (id) {
        pendingDeleteId = id;
        var cat = null;
        for (var i = 0; i < categoriesData.length; i++) {
            if (categoriesData[i].id === id) { cat = categoriesData[i]; break; }
        }
        document.getElementById('fmModalMsg').textContent = 'Delete category "' + (cat ? cat.category_name : id) + '"? This action cannot be undone.';
        document.getElementById('fmModalOverlay').classList.add('fm-modal-active');
    };

    document.getElementById('fmModalCancel').addEventListener('click', function () {
        pendingDeleteId = null;
        document.getElementById('fmModalOverlay').classList.remove('fm-modal-active');
    });

    document.getElementById('fmModalOverlay').addEventListener('click', function (e) {
        if (e.target === this) {
            pendingDeleteId = null;
            this.classList.remove('fm-modal-active');
        }
    });

    document.getElementById('fmModalConfirm').addEventListener('click', function () {
        if (!pendingDeleteId) return;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';

        var fd = buildFormData({ category_id: pendingDeleteId });

        ajax('POST', BASE + 'fee_management/delete_category', fd)
            .then(function (r) {
                refreshCsrf(r);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-trash-o"></i> Delete';
                document.getElementById('fmModalOverlay').classList.remove('fm-modal-active');
                if (r.status === 'success') {
                    showToast(r.message || 'Category deleted.', 'success');
                    if (editingId === pendingDeleteId) resetForm();
                    pendingDeleteId = null;
                    loadCategories();
                } else {
                    showToast(r.message || 'Failed to delete category.', 'error');
                    pendingDeleteId = null;
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-trash-o"></i> Delete';
                document.getElementById('fmModalOverlay').classList.remove('fm-modal-active');
                showToast('Server error. Please try again.', 'error');
                pendingDeleteId = null;
            });
    });

    /* ── Helpers ── */
    function getTypeBadgeClass(type) {
        switch (type) {
            case 'Academic':         return 'fm-badge-teal';
            case 'Transport':        return 'fm-badge-gold';
            case 'Extra-curricular': return 'fm-badge-green';
            default:                 return 'fm-badge-muted';
        }
    }

    function escHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s));
        return d.innerHTML;
    }

    function escAttr(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:ital,wght@0,600;0,700;1,600&display=swap');

:root {
    --fm-navy: var(--t1, #0f1f3d);
    --fm-teal: var(--gold, #0f766e);
    --fm-teal2: var(--gold2, #0d6b63);
    --fm-sky: var(--gold-dim, rgba(15,118,110,.10));
    --fm-gold: #d97706;
    --fm-red: #E05C6F;
    --fm-green: #15803d;
    --fm-text: var(--t1, #1a2940);
    --fm-muted: var(--t3, #64748b);
    --fm-border: var(--border, #d1e8e4);
    --fm-bg: var(--bg, #f0f6f5);
    --fm-card: var(--bg2, #ffffff);
    --fm-shadow: var(--sh, 0 2px 16px rgba(13,115,119,.10));
    --fm-radius: var(--r, 12px);
}

* { box-sizing: border-box; }

.fm-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--fm-bg);
    color: var(--fm-text);
    padding: 18px 20px 40px;
    min-height: 100vh;
    font-size: .8rem;
}

/* ── Top bar ── */
.fm-topbar { margin-bottom: 24px; }

.fm-page-title {
    font-family: 'Fraunces', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--fm-navy);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 6px;
}
.fm-page-title i { color: var(--fm-teal); }

.fm-breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .78rem;
    color: var(--fm-muted);
    list-style: none;
    margin: 0;
    padding: 0;
}
.fm-breadcrumb a {
    color: var(--fm-teal);
    text-decoration: none;
    font-weight: 600;
}
.fm-breadcrumb li::before {
    content: '/';
    margin-right: 6px;
    color: var(--fm-border);
}
.fm-breadcrumb li:first-child::before { display: none; }

/* ── Stats ── */
.fm-stats {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}
.fm-stat {
    background: var(--fm-card);
    border: 1px solid var(--fm-border);
    border-radius: var(--fm-radius);
    box-shadow: var(--fm-shadow);
    padding: 14px 20px;
    flex: 1;
    min-width: 120px;
}
.fm-stat-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: var(--fm-muted);
    margin-bottom: 4px;
}
.fm-stat-value {
    font-family: 'Fraunces', serif;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--fm-navy);
}
.fm-stat.teal .fm-stat-value { color: var(--fm-teal); }
.fm-stat.gold .fm-stat-value { color: var(--fm-gold); }
.fm-stat.green .fm-stat-value { color: var(--fm-green); }

/* ── Card ── */
.fm-card {
    background: var(--fm-card);
    border-radius: var(--fm-radius);
    box-shadow: var(--fm-shadow);
    border: 1px solid var(--fm-border);
    margin-bottom: 22px;
    overflow: hidden;
}
.fm-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--fm-border);
    background: var(--fm-card);
}
.fm-card-head h3 {
    font-family: 'Fraunces', serif;
    font-size: .92rem;
    font-weight: 700;
    color: var(--fm-navy);
    margin: 0;
}
.fm-card-head i {
    color: var(--fm-teal);
    font-size: .92rem;
}
.fm-card-body { padding: 18px; }

/* ── Form grid ── */
.fm-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    align-items: start;
}
.fm-form-col-full { grid-column: 1 / -1; }

@media (max-width: 860px) {
    .fm-form-grid { grid-template-columns: 1fr 1fr; }
    .fm-form-col:nth-child(3) { grid-column: 1 / -1; }
}
@media (max-width: 560px) {
    .fm-form-grid { grid-template-columns: 1fr; }
    .fm-form-col:nth-child(3) { grid-column: auto; }
}

.fm-label {
    display: block;
    font-size: .73rem;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: var(--fm-muted);
    margin-bottom: 6px;
}
.fm-req { color: var(--fm-red); }
.fm-opt { font-weight: 500; text-transform: none; font-size: .62rem; }

.fm-input,
.fm-select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1.5px solid var(--fm-border);
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .8rem;
    color: var(--fm-text);
    background: var(--fm-sky);
    outline: none;
    transition: border-color .13s, box-shadow .13s;
}
.fm-textarea {
    height: auto;
    padding: 10px 12px;
    resize: vertical;
    min-height: 48px;
}
.fm-input:focus,
.fm-select:focus {
    border-color: var(--fm-teal);
    box-shadow: 0 0 0 3px rgba(13,115,119,.12);
    background: var(--fm-card);
}

.fm-form-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
    align-items: center;
}

/* ── Checkbox group ── */
.fm-checkbox-group {
    border: 1.5px solid var(--fm-border);
    border-radius: 10px;
    padding: 14px 16px;
    background: var(--fm-sky);
    max-height: 220px;
    overflow-y: auto;
}
.fm-checkbox-section { margin-bottom: 12px; }
.fm-checkbox-section:last-child { margin-bottom: 0; }
.fm-checkbox-section-head {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--fm-navy);
    margin-bottom: 8px;
    padding-bottom: 5px;
    border-bottom: 1px dashed var(--fm-border);
}
.fm-checkbox-section-head i {
    color: var(--fm-teal);
    font-size: .68rem;
    margin-right: 4px;
}

.fm-check-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 6px;
    border-radius: 6px;
    cursor: pointer;
    transition: background .1s;
    font-size: .8rem;
}
.fm-check-label:hover { background: var(--fm-sky); }

.fm-check-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.fm-check-box {
    width: 16px;
    height: 16px;
    border: 2px solid var(--fm-border);
    border-radius: 4px;
    flex-shrink: 0;
    position: relative;
    transition: all .13s;
    background: var(--fm-card);
}
.fm-check-input:checked + .fm-check-box {
    background: var(--fm-teal);
    border-color: var(--fm-teal);
}
.fm-check-input:checked + .fm-check-box::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1px;
    width: 5px;
    height: 9px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.fm-check-input:focus + .fm-check-box {
    box-shadow: 0 0 0 3px rgba(13,115,119,.15);
}
.fm-check-text {
    flex: 1;
    color: var(--fm-text);
    font-weight: 500;
}

.fm-empty-sm {
    font-size: .75rem;
    color: var(--fm-muted);
    padding: 8px 0;
}
.fm-empty-sm i { margin-right: 4px; }

/* ── Buttons ── */
.fm-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s;
    text-decoration: none;
    white-space: nowrap;
    line-height: 1;
}
.fm-btn:disabled {
    opacity: .55;
    cursor: not-allowed;
}
.fm-btn-primary {
    background: var(--fm-teal);
    color: #fff;
    height: 40px;
}
.fm-btn-primary:hover:not(:disabled) { background: #0a5c60; }

.fm-btn-outline {
    background: transparent;
    color: var(--fm-muted);
    border: 1.5px solid var(--fm-border);
    height: 40px;
}
.fm-btn-outline:hover { border-color: var(--fm-muted); color: var(--fm-text); }

.fm-btn-teal {
    background: var(--fm-sky);
    color: var(--fm-teal);
}
.fm-btn-teal:hover { background: var(--fm-sky); }

.fm-btn-danger {
    background: var(--fm-red);
    color: #fff;
}
.fm-btn-danger:hover:not(:disabled) { background: #c0392b; }

.fm-btn-sm { padding: 5px 11px; font-size: 12px; }
.fm-btn-xs { padding: 4px 9px; font-size: 11.5px; border-radius: 6px; }

/* ── Table ── */
.fm-table-wrap { overflow-x: auto; }

.fm-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .8rem;
}
.fm-table th {
    background: var(--fm-sky);
    color: var(--fm-muted);
    padding: 9px 14px;
    text-align: left;
    font-size: .7rem;
    letter-spacing: .4px;
    text-transform: uppercase;
    font-weight: 700;
    white-space: nowrap;
}
.fm-table td {
    padding: 9px 14px;
    border-bottom: 1px solid var(--fm-border);
    vertical-align: middle;
}
.fm-table tbody tr:hover { background: var(--fm-sky); }
.fm-table tbody tr:last-child td { border-bottom: none; }

.fm-cell-muted { color: var(--fm-muted); font-size: 12px; }
.fm-cell-name { font-weight: 600; font-size: 13px; }
.fm-cell-titles { max-width: 280px; }
.fm-cell-actions { white-space: nowrap; }
.fm-muted-text { color: var(--fm-muted); font-size: 11px; font-style: italic; }

/* ── Badge ── */
.fm-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .68rem;
    font-weight: 700;
    gap: 4px;
}
.fm-badge-xs {
    padding: 2px 7px;
    font-size: .6rem;
    border-radius: 12px;
}
.fm-badge-teal { background: var(--fm-sky); color: var(--fm-teal); }
.fm-badge-gold { background: #fef3db; color: #b5730a; }
.fm-badge-green { background: #e6f9ed; color: #1a8a44; }
.fm-badge-red { background: #fde8e8; color: var(--fm-red); }
.fm-badge-muted { background: #f0f0f0; color: var(--fm-muted); }

/* ── Empty & Loading ── */
.fm-empty {
    text-align: center;
    padding: 40px 20px;
    color: var(--fm-muted);
    font-size: .8rem;
}
.fm-empty i {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
    opacity: .4;
}
.fm-loading {
    text-align: center;
    padding: 30px 20px;
    color: var(--fm-muted);
    font-size: .8rem;
}
.fm-loading i { margin-right: 6px; }

/* ── Toast ── */
.fm-toast-wrap {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
}
.fm-toast {
    padding: 12px 18px;
    border-radius: 8px;
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    font-family: 'Plus Jakarta Sans', sans-serif;
    box-shadow: 0 4px 20px rgba(0,0,0,.2);
    animation: fm-toast-in .25s ease;
    pointer-events: auto;
    max-width: 320px;
}
.fm-toast.success { background: var(--fm-green); }
.fm-toast.error { background: var(--fm-red); }

@keyframes fm-toast-in {
    from { transform: translateX(40px); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

/* ── Confirm Modal ── */
.fm-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,31,61,.45);
    z-index: 99998;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity .2s, visibility .2s;
}
.fm-modal-overlay.fm-modal-active {
    opacity: 1;
    visibility: visible;
}
.fm-modal {
    background: var(--fm-card);
    border-radius: var(--fm-radius);
    box-shadow: 0 8px 40px rgba(15,31,61,.25);
    width: 400px;
    max-width: 92vw;
    overflow: hidden;
    transform: scale(.92);
    transition: transform .2s;
}
.fm-modal-overlay.fm-modal-active .fm-modal {
    transform: scale(1);
}
.fm-modal-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 20px;
    background: linear-gradient(135deg, #fde8e8 0%, #fff 100%);
    border-bottom: 1px solid var(--fm-border);
    font-family: 'Fraunces', serif;
    font-size: .92rem;
    font-weight: 700;
    color: var(--fm-red);
}
.fm-modal-head i { font-size: 15px; }
.fm-modal-body {
    padding: 20px;
    font-size: .8rem;
    color: var(--fm-text);
    line-height: 1.55;
}
.fm-modal-foot {
    padding: 12px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid var(--fm-border);
    background: var(--fm-sky);
}

/* ── Responsive ── */
@media (max-width: 767px) {
    .fm-wrap { padding: 16px 12px 50px; }
    .fm-page-title { font-size: 21px; }
    .fm-stats { gap: 10px; }
    .fm-stat { min-width: 100px; padding: 10px 14px; }
    .fm-stat-value { font-size: 22px; }
    .fm-card-body { padding: 16px; }
    .fm-table th,
    .fm-table td { padding: 7px 10px; }
    .fm-cell-titles { max-width: 160px; }
}

@media (max-width: 479px) {
    .fm-stats { flex-direction: column; }
    .fm-stat { min-width: unset; }
    .fm-form-actions { flex-direction: column; }
    .fm-form-actions .fm-btn { width: 100%; justify-content: center; }
    .fm-modal { width: 96vw; }
}
</style>
