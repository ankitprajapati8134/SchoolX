<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div class="content-wrapper">
  <section class="content">

    <div class="mc-header">
      <div class="mc-header-inner">
        <div class="mc-header-left">
          <div class="mc-header-icon">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h10M4 18h7" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <div>
            <h1 class="mc-title">Class Management</h1>
            <p class="mc-subtitle">Manage classes, sections &amp; subjects</p>
          </div>
        </div>

        <div class="mc-dropdown-wrap" id="addClassWrap">
          <button type="button" class="mc-btn mc-btn-add" id="btnAddClass">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            <span id="addClassBtnText">Add New Class</span>
            <svg class="mc-chevron" width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <div class="mc-dropdown" id="classDropdown"></div>
        </div>
      </div>
    </div>

    <div class="mc-stats">
      <div class="mc-stat-card">
        <div class="mc-stat-icon mc-stat-icon--classes">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
        </div>
        <div>
          <span class="mc-stat-value" id="statTotalClasses">&mdash;</span>
          <span class="mc-stat-label">Total Classes</span>
        </div>
      </div>
      <div class="mc-stat-card">
        <div class="mc-stat-icon mc-stat-icon--sections">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0H5a2 2 0 01-2-2v-4m6 6h10a2 2 0 002-2v-4" stroke="currentColor" stroke-width="2"/></svg>
        </div>
        <div>
          <span class="mc-stat-value" id="statTotalSections">&mdash;</span>
          <span class="mc-stat-label">Total Sections</span>
        </div>
      </div>
    </div>

    <div class="mc-loader" id="pageLoader">
      <div class="mc-spinner"></div>
      <span>Loading classes…</span>
    </div>

    <div class="mc-grid" id="classGrid" style="display:none;"></div>

    <div class="mc-empty" id="emptyState" style="display:none;">
      <svg width="56" height="56" fill="none" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h10M4 18h7" stroke="#D4A017" stroke-width="1.5" stroke-linecap="round"/></svg>
      <h3>No classes yet</h3>
      <p>Click <strong>"Add New Class"</strong> above to create your first class.</p>
    </div>

    <div class="mc-toast" id="toast"><span id="toastMsg"></span></div>

  </section>
</div>






<script>
document.addEventListener('DOMContentLoaded', function () {

    var BASE = '<?= base_url() ?>';

    /* ─── Helpers ─────────────────────────────────────────────── */
    function $(id) { return document.getElementById(id); }

    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    function toast(msg) {
        $('toastMsg').textContent = msg;
        $('toast').classList.add('show');
        setTimeout(function () { $('toast').classList.remove('show'); }, 3000);
    }

    /*
     * CSRF — OPTION A (production-safe)
     * ─────────────────────────────────────────────────────────────
     *
     * Token is sent TWO ways on every POST:
     *
     *   1. FormData field  'csrf_token' = CSRF_HASH
     *      → CI's built-in csrf_protection filter reads this,
     *        validates it, then REMOVES it from $_POST.
     *        This satisfies CI's requirement that every POST
     *        carries the token, so no 403 from CI's filter.
     *
     *   2. Request header  'X-CSRF-Token' = CSRF_HASH
     *      → CI's filter completely ignores request headers,
     *        so this value is untouched and still readable
     *        when MY_Controller.verify_csrf() runs inside
     *        the controller method.
     *
     * Why both? Because CI consumes/removes the body field
     * before the controller runs, so the controller can only
     * safely read the header. The body field is there purely
     * to keep CI's built-in filter happy.
     *
     * Works identically in local dev and production.
     * No config.php changes required.
     * ─────────────────────────────────────────────────────────────
     */
    var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?php echo $this->security->get_csrf_hash(); ?>';

    /**
     * post(url, params)
     *
     * Central AJAX helper used by every request on this page.
     * Sends CSRF token in BOTH body field AND request header.
     */
    function post(url, params) {
        var fd = new FormData();

        // ① Body field — satisfies CI's built-in csrf_protection filter
        fd.append(CSRF_NAME, CSRF_HASH);

        if (params) {
            Object.keys(params).forEach(function (k) {
                fd.append(k, params[k]);
            });
        }

        return fetch(url, {
            method: 'POST',
            body:   fd,
            headers: {
                // ② Header — readable by MY_Controller.verify_csrf()
                //    after CI's filter has already consumed the body field
                'X-CSRF-Token': CSRF_HASH
            }
        })
        .then(function (r) {
            return r.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.warn('[POST ' + url.split('/').pop() + '] non-JSON:', text.substring(0, 300));
                    return text;
                }
            });
        });
    }


    /* ═══════════════════════════════════════════════
       1. LOAD CLASS GRID
    ═══════════════════════════════════════════════ */
 function loadClassGrid() {
    $('pageLoader').style.display = '';
    $('classGrid').style.display  = 'none';
    $('emptyState').style.display = 'none';

    post(BASE + 'classes/fetch_classes_grid')   // ← use post() like everything else
        .then(function(classes) {
            $('pageLoader').style.display = 'none';

            if (typeof classes === 'string') {
                console.error('[manage_classes] Non-JSON response:', classes.substring(0, 500));
                $('emptyState').style.display = '';
                $('statTotalClasses').textContent  = '0';
                $('statTotalSections').textContent = '0';
                toast('Error loading classes — check console (F12)');
                return;
            }

            if (!Array.isArray(classes) || classes.length === 0) {
                $('emptyState').style.display = '';
                $('statTotalClasses').textContent  = '0';
                $('statTotalSections').textContent = '0';
                return;
            }

            $('statTotalClasses').textContent = classes.length;
            $('classGrid').style.display = '';

            var grid          = $('classGrid');
            grid.innerHTML    = '';
            var totalSections = 0;
            var pending       = classes.length;

            classes.forEach(function (cls) {
                var safeId = cls.key.replace(/[^a-zA-Z0-9]/g, '-');

                grid.insertAdjacentHTML('beforeend',
                    '<div class="mc-card" data-class="' + escHtml(cls.key) + '">' +
                      '<div class="mc-card-top">' +
                        '<h3 class="mc-card-class">' + escHtml(cls.label) + '</h3>' +
                        '<div class="mc-card-sections" id="sec-' + safeId + '">' +
                          '<span class="mc-section-badge">Loading…</span>' +
                        '</div>' +
                      '</div>' +
                      '<div class="mc-card-body">' +
                        '<span class="mc-card-meta" id="meta-' + safeId + '">Fetching sections…</span>' +
                      '</div>' +
                    '</div>'
                );

                post(BASE + 'classes/fetch_class_sections', { class_name: cls.key })
                    .then(function (sections) {
                        var secEl  = $('sec-'  + safeId);
                        var metaEl = $('meta-' + safeId);
                        if (!secEl) return;

                        if (!Array.isArray(sections) || sections.length === 0) {
                            secEl.innerHTML = '<span class="mc-section-badge">No sections</span>';
                            if (metaEl) metaEl.innerHTML = 'No sections added yet';
                        } else {
                            var badges       = '';
                            var studentTotal = 0;

                            sections.forEach(function (sec) {
                                var count = sec.strength || 0;
                                badges += '<span class="mc-section-badge">' +
                                    escHtml(sec.name || 'Section') +
                                    (count > 0 ? ' (' + count + ')' : '') +
                                    '</span>';
                                studentTotal += count;
                                totalSections++;
                            });

                            secEl.innerHTML = badges;
                            if (metaEl) {
                                metaEl.innerHTML =
                                    '<strong>' + sections.length + '</strong> section' +
                                    (sections.length > 1 ? 's' : '') +
                                    ' &middot; <strong>' + studentTotal + '</strong> student' +
                                    (studentTotal !== 1 ? 's' : '');
                            }
                        }

                        pending--;
                        if (pending <= 0) $('statTotalSections').textContent = totalSections;
                    })
                    .catch(function () {
                        var secEl = $('sec-' + safeId);
                        if (secEl) secEl.innerHTML = '<span class="mc-section-badge">Error</span>';
                        pending--;
                        if (pending <= 0) $('statTotalSections').textContent = totalSections;
                    });
            });
        })
        .catch(function (err) {
            $('pageLoader').style.display = 'none';
            $('emptyState').style.display = '';
            console.error('[manage_classes] Grid load failed:', err);
            toast('Failed to load classes.');
        });
}
    loadClassGrid();


    /* ═══════════════════════════════════════════════
       2. CARD CLICK → Navigate to class profile
    ═══════════════════════════════════════════════ */
    $('classGrid').addEventListener('click', function (e) {
        var card = e.target.closest('.mc-card');
        if (!card) return;

        var classKey = card.dataset.class;
        var slug     = classKey.replace(/^Class\s+/i, '').trim();

        window.location.href = BASE + 'classes/view/' + encodeURIComponent(slug);
    });


    /* ═══════════════════════════════════════════════
       3. ADD NEW CLASS DROPDOWN
    ═══════════════════════════════════════════════ */
    var classListCache = null;

    $('btnAddClass').addEventListener('click', function (e) {
        e.stopPropagation();
        var wrap = $('addClassWrap');

        if (wrap.classList.contains('open')) {
            wrap.classList.remove('open');
            return;
        }

        if (classListCache) {
            renderDropdown(classListCache);
            wrap.classList.add('open');
        } else {
            post(BASE + 'classes/get_class_details')
                .then(function (data) {
                    classListCache = Array.isArray(data) ? data : [];
                    renderDropdown(classListCache);
                    wrap.classList.add('open');
                })
                .catch(function () { toast('Failed to load class list.'); });
        }
    });

    function renderDropdown(classes) {
        var dd = $('classDropdown');

        if (!classes.length) {
            dd.innerHTML = '<div class="mc-dropdown-empty">No classes found in Subject List</div>';
            return;
        }

        var html = '';
        classes.forEach(function (cls) {
            html += '<div class="mc-dropdown-item" data-label="' + escHtml(cls.label) + '">' +
                escHtml(cls.label) + '</div>';
        });
        dd.innerHTML = html;
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#addClassWrap')) {
            $('addClassWrap').classList.remove('open');
        }
    });

    $('classDropdown').addEventListener('click', function (e) {
        var item = e.target.closest('.mc-dropdown-item');
        if (!item) return;

        var classLabel = item.dataset.label;
        $('addClassWrap').classList.remove('open');
        $('addClassBtnText').textContent = classLabel;

        toast('Creating ' + classLabel + '…');

        post(BASE + 'classes/ensure_class_exists', { class_name: classLabel })
            .then(function (res) {
                if (res && res.status === 'success') {
                    var slug = classLabel.replace(/^Class\s+/i, '').trim();
                    window.location.href = BASE + 'classes/view/' + encodeURIComponent(slug);
                } else {
                    toast((res && res.message) ? res.message : 'Unable to create class');
                }
            })
            .catch(function () { toast('Failed to create class.'); });
    });

});
</script>

<style>
/* ── Base ────────────────────────────────────── */
.content-wrapper { background: var(--bg); min-height: 100vh; }
.content { max-width: 1200px; margin: 0 auto; padding: 24px 20px 60px; }

/* ── Header ──────────────────────────────────── */
.mc-header {
  background: linear-gradient(135deg, var(--gold) 0%, #e9a200 100%);
  border-radius: 14px;
  padding: 22px 28px;
  margin-bottom: 24px;
}
.mc-header-inner {
  display: flex; align-items: center;
  justify-content: space-between;
  flex-wrap: wrap; gap: 16px;
}
.mc-header-left { display: flex; align-items: center; gap: 14px; }
.mc-header-icon {
  width: 44px; height: 44px;
  background: rgba(255,255,255,.18);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}
.mc-title { font: 700 22px/1.2 var(--font-b); color: #fff; margin: 0; }
.mc-subtitle { font: 400 13px/1.4 var(--font-b); color: rgba(255,255,255,.78); margin: 4px 0 0; }

/* ── Add Class Button + Dropdown ─────────────── */
.mc-dropdown-wrap { position: relative; }
.mc-btn-add {
  display: inline-flex; align-items: center; gap: 7px;
  font: 600 13px/1 var(--font-b);
  background: #fff; color: #D49800;
  border: none; border-radius: 10px;
  padding: 10px 16px; cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  transition: all var(--ease);
  white-space: nowrap;
}
.mc-btn-add:hover { background: var(--gold-dim); transform: translateY(-1px); }
.mc-chevron { transition: transform var(--ease); }
.mc-dropdown-wrap.open .mc-chevron { transform: rotate(180deg); }

.mc-dropdown {
  display: none;
  position: absolute; top: calc(100% + 6px); right: 0;
  background: var(--bg2); border-radius: 10px;
  min-width: 200px; max-height: 260px; overflow-y: auto;
  box-shadow: var(--sh);
  z-index: 1000; padding: 4px 0;
}
.mc-dropdown-wrap.open .mc-dropdown { display: block; animation: mcFadeDown .15s ease; }
@keyframes mcFadeDown { from { opacity:0; transform: translateY(-6px); } to { opacity:1; transform: translateY(0); } }

.mc-dropdown-item {
  padding: 10px 16px; font: 500 13px/1.3 var(--font-b);
  color: var(--t1); cursor: pointer;
  transition: background var(--ease);
}
.mc-dropdown-item:hover { background: rgba(245,175,0,.10); }
.mc-dropdown-empty {
  padding: 14px 16px; font: 400 13px/1.3 var(--font-b);
  color: var(--t3); text-align: center;
}

/* ── Stats ───────────────────────────────────── */
.mc-stats { display: flex; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
.mc-stat-card {
  flex: 1 1 180px;
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: 10px; padding: 16px 20px;
  display: flex; align-items: center; gap: 14px;
  box-shadow: var(--sh);
}
.mc-stat-icon {
  width: 42px; height: 42px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mc-stat-icon--classes  { background: var(--gold-dim); color: var(--gold); }
.mc-stat-icon--sections { background: #E0F2FE; color: #0284C7; }
.mc-stat-value { display: block; font: 700 22px/1.2 var(--font-b); color: var(--t1); }
.mc-stat-label { font: 400 12.5px/1.4 var(--font-b); color: var(--t3); }

/* ── Loader ──────────────────────────────────── */
.mc-loader {
  display: flex; flex-direction: column; align-items: center;
  padding: 60px 20px; color: var(--t3);
  font: 400 14px/1.4 var(--font-b); gap: 14px;
}
.mc-spinner {
  width: 32px; height: 32px;
  border: 3px solid var(--border);
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: mcSpin .7s linear infinite;
}
@keyframes mcSpin { to { transform: rotate(360deg); } }

/* ── Grid ────────────────────────────────────── */
.mc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 22px;
}

/* ── Class Card ──────────────────────────────── */
.mc-card {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: 14px; overflow: hidden;
  transition: all var(--ease); cursor: pointer;
}
.mc-card:hover {
  border-color: var(--gold);
  box-shadow: var(--sh);
  transform: translateY(-3px);
}
.mc-card-top {
  background: linear-gradient(135deg, var(--gold) 0%, #FFCC4D 100%);
  padding: 24px 20px 20px; position: relative; overflow: hidden;
}
.mc-card-top::after {
  content: ''; position: absolute; right: -20px; top: -20px;
  width: 80px; height: 80px;
  background: rgba(255,255,255,.10); border-radius: 50%;
}
.mc-card-class { font: 700 20px/1.2 var(--font-b); color: #fff; margin: 0; }
.mc-card-sections {
  margin-top: 10px; display: flex; flex-wrap: wrap; gap: 6px;
}
.mc-section-badge {
  display: inline-block;
  background: rgba(255,255,255,.22); color: #fff;
  font: 600 11.5px/1 var(--font-b);
  padding: 4px 10px; border-radius: 6px;
}
.mc-card-body { padding: 14px 20px 18px; }
.mc-card-meta {
  font: 400 12.5px/1.6 var(--font-b); color: var(--t3);
}
.mc-card-meta strong { color: var(--t1); font-weight: 600; }

/* ── Empty State ─────────────────────────────── */
.mc-empty {
  text-align: center; padding: 60px 20px; color: var(--t3);
}
.mc-empty h3 { font: 600 18px/1.3 var(--font-b); color: var(--t1); margin: 16px 0 6px; }
.mc-empty p  { font: 400 14px/1.5 var(--font-b); }

/* ── Toast ───────────────────────────────────── */
.mc-toast {
  position: fixed; bottom: 32px; left: 50%;
  transform: translateX(-50%) translateY(80px);
  background: var(--bg3); color: var(--t1);
  font: 500 14px/1.4 var(--font-b);
  padding: 12px 28px; border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.2);
  z-index: 99999; opacity: 0;
  transition: all .3s ease; pointer-events: none;
}
.mc-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* ── Responsive ──────────────────────────────── */
@media (max-width: 600px) {
  .mc-header { padding: 16px; }
  .mc-title { font-size: 18px; }
  .mc-grid { grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; }
}
</style>