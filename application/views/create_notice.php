<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <div class="cn-wrap">
        <div class="cn-page-head">
            <h2><i class="fa fa-bell"></i> Create Notice</h2>
            <a href="<?= base_url('NoticeAnnouncement') ?>" class="cn-back-btn">
                <i class="fa fa-list-ul"></i> All Notices
            </a>
        </div>

        <div class="cn-grid">
            <!-- LEFT -->
            <div>
                <div class="cn-card">
                    <div class="cn-card-head">
                        <div class="cn-card-icon"><i class="fa fa-file-text-o"></i></div>
                        <div class="cn-card-head-text">
                            <h4>Notice Details</h4>
                            <p>Fill in the title, message and choose recipients</p>
                        </div>
                    </div>
                    <div class="cn-card-body">
                        <form id="cnForm" method="post"
                            action="<?= site_url('NoticeAnnouncement/create_notice') ?>"
                            enctype="multipart/form-data">

                            <div class="cn-field">
                                <label class="cn-label">Title <span class="req">*</span></label>
                                <input type="text" class="cn-input" name="title" required
                                    placeholder="e.g. School Closure on Republic Day">
                            </div>
                            <div class="cn-field">
                                <label class="cn-label">Description <span class="req">*</span></label>
                                <textarea class="cn-textarea" name="description" required
                                    placeholder="Write your notice message here…"></textarea>
                            </div>

                            <input type="hidden" name="count"
                                value="<?= isset($notices['Count']) ? (int)$notices['Count'] : 0 ?>">
                            <input type="hidden" name="to_id_json" id="cnToIdJson" value="{}">

                            <div class="cn-divider"></div>
                            <label class="cn-label">Recipients <span class="req">*</span></label>

                            <div class="cn-recip-row">
                                <!-- Bulk -->
                                <div class="cn-check-group">
                                    <div class="cn-group-title"><i class="fa fa-users"></i> Bulk</div>
                                    <?php foreach (['All School', 'All Students', 'All Teachers', 'All Admins'] as $opt): ?>
                                        <div class="cn-check-row">
                                            <input type="checkbox" name="to_option[]" value="<?= $opt ?>"
                                                id="cnChk_<?= str_replace(' ', '_', $opt) ?>">
                                            <label for="cnChk_<?= str_replace(' ', '_', $opt) ?>"><?= $opt ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Class -->
                                <div class="cn-check-group">
                                    <div class="cn-group-title"><i class="fa fa-graduation-cap"></i> By Class</div>
                                    <label class="cn-label" style="margin-top:4px">Select Class / Section</label>
                                    <select class="cn-select" id="cnClassDD">
                                        <option value="">— Select —</option>
                                        <?php foreach ($classes as $val => $lbl): ?>
                                            <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($lbl) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Search -->
                                <div class="cn-check-group">
                                    <div class="cn-group-title"><i class="fa fa-search"></i> Individual</div>
                                    <div class="cn-search-wrap">
                                        <i class="fa fa-search cn-search-icon"></i>
                                        <input type="text" class="cn-input" id="cnSearch"
                                            placeholder="Name or ID…" autocomplete="off">
                                        <div id="cnSearchResults"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="cn-tags-wrap" id="cnTagsWrap">
                                <div class="cn-tags-title">
                                    <i class="fa fa-check-circle" style="color:var(--green)"></i>
                                    Selected Recipients
                                </div>
                                <div class="cn-tags-list" id="cnTagsList"></div>
                            </div>

                            <button type="submit" class="cn-submit" id="cnSubmitBtn">
                                <i class="fa fa-paper-plane"></i> Send Notice
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div>
                <div class="cn-card">
                    <div class="cn-card-head">
                        <div class="cn-card-icon" style="background:rgba(74,181,227,.12);color:#2a8fbf">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div class="cn-card-head-text">
                            <h4>Recent Notices</h4>
                            <p>Last 5 sent</p>
                        </div>
                    </div>
                    <div class="cn-card-body" style="padding:14px 18px">
                        <div id="cnRecentList">
                            <p style="font-size:12px;color:var(--muted);text-align:center;padding:12px 0">
                                <i class="fa fa-spinner fa-spin"></i> Loading…
                            </p>
                        </div>
                    </div>
                </div>

                <div class="cn-card" style="margin-top:16px">
                    <div class="cn-card-head">
                        <div class="cn-card-icon" style="background:rgba(61,214,140,.12);color:#1fa86a">
                            <i class="fa fa-bar-chart"></i>
                        </div>
                        <div class="cn-card-head-text">
                            <h4>Session Stats</h4>
                            <p>Notice summary</p>
                        </div>
                    </div>
                    <div class="cn-card-body" style="padding:14px 18px">
                        <div class="cn-stat-row">
                            <span class="cn-stat-label">Total Notices</span>
                            <span class="cn-stat-val" style="color:var(--gold)">
                                <?= isset($notices['Count']) ? (int)$notices['Count'] : 0 ?>
                            </span>
                        </div>
                        <div class="cn-stat-row">
                            <span class="cn-stat-label">Sent Today</span>
                            <span class="cn-stat-val" id="statToday" style="color:var(--green)">—</span>
                        </div>
                        <div class="cn-stat-row">
                            <span class="cn-stat-label">This Week</span>
                            <span class="cn-stat-val" id="statWeek" style="color:#2a8fbf">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="cnToastWrap"></div>


<script>
    (function() {
        'use strict';
        var SITE = '<?= rtrim(site_url(), '/') ?>';
        var selectedSet = {},
            searchTimer;
        var $form = document.getElementById('cnForm'),
            $tagWrap = document.getElementById('cnTagsWrap'),
            $tagList = document.getElementById('cnTagsList'),
            $hidden = document.getElementById('cnToIdJson'),
            $classDD = document.getElementById('cnClassDD'),
            $search = document.getElementById('cnSearch'),
            $results = document.getElementById('cnSearchResults'),
            $submit = document.getElementById('cnSubmitBtn');

        function toast(msg, type) {
            var el = document.createElement('div');
            el.className = 'cn-toast cn-toast-' + (type || 'info');
            el.innerHTML = '<i class="fa fa-' + (type === 'success' ? 'check' : 'times') + '-circle"></i> ' + msg;
            document.getElementById('cnToastWrap').appendChild(el);
            setTimeout(function() {
                el.classList.add('hide');
                setTimeout(function() {
                    el.remove();
                }, 260);
            }, 3000);
        }

        function sync() {
            $hidden.value = JSON.stringify(selectedSet);
        }

        function addTag(key, label) {
            if (selectedSet[key] !== undefined) return;
            selectedSet[key] = label;
            sync();
            var dot = /^All/.test(key) ? '#e05c6f' : /^STU/.test(key) ? '#3dd68c' :
                /^TEA/.test(key) ? '#4ab5e3' : /^ADM/.test(key) ? '#F5AF00' : '#F5AF00';
            var span = document.createElement('span');
            span.className = 'cn-tag';
            span.dataset.key = key;
            span.innerHTML =
                '<span class="cn-tag-dot" style="background:' + dot + '"></span>' +
                '<span style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="' + label + '">' + label + '</span>' +
                '<button type="button" class="cn-tag-remove"><i class="fa fa-times" style="font-size:9px"></i></button>';
            span.querySelector('.cn-tag-remove').addEventListener('click', function() {
                removeTag(key);
                var cb = document.querySelector('input[type=checkbox][value="' + label + '"]');
                if (cb) cb.checked = false;
            });
            $tagList.appendChild(span);
            $tagWrap.style.display = 'block';
            updateControls();
        }

        function removeTag(key) {
            delete selectedSet[key];
            sync();
            var el = $tagList.querySelector('[data-key="' + key + '"]');
            if (el) el.remove();
            if (!Object.keys(selectedSet).length) $tagWrap.style.display = 'none';
            updateControls();
        }

        function updateControls() {
            var isAll = selectedSet['All School'] !== undefined;
            var isAllStu = selectedSet['All Students'] !== undefined;
            document.querySelectorAll('input[type=checkbox][name="to_option[]"]').forEach(function(cb) {
                cb.disabled = isAll && cb.value !== 'All School';
            });
            $classDD.disabled = isAll || isAllStu;
            $search.disabled = isAll;
        }

        document.querySelectorAll('input[type=checkbox][name="to_option[]"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var val = cb.value;
                if (val === 'All School' && cb.checked) {
                    Object.keys(selectedSet).forEach(function(k) {
                        if (k !== 'All School') removeTag(k);
                    });
                    document.querySelectorAll('input[type=checkbox][name="to_option[]"]').forEach(function(c) {
                        if (c.value !== 'All School') c.checked = false;
                    });
                    $classDD.value = '';
                    $search.value = '';
                    $results.style.display = 'none';
                    addTag(val, val);
                } else if (cb.checked) {
                    if (selectedSet['All School'] !== undefined) {
                        document.querySelector('input[value="All School"]').checked = false;
                        removeTag('All School');
                    }
                    addTag(val, val);
                } else {
                    removeTag(val);
                }
                updateControls();
            });
        });

        $classDD.addEventListener('change', function() {
            var v = $classDD.value;
            if (!v) return;
            var parts = v.split('|');
            var display = (parts[0] || '').trim() + ' / ' + (parts[1] || '').trim();
            addTag(v, display);
            $classDD.value = '';
        });

        $search.addEventListener('input', function() {
            clearTimeout(searchTimer);
            var q = $search.value.trim();
            if (!q) {
                $results.style.display = 'none';
                $results.innerHTML = '';
                return;
            }
            searchTimer = setTimeout(function() {
                fetch(SITE + '/NoticeAnnouncement/search_users?query=' + encodeURIComponent(q))
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        var isExStu = selectedSet['All Students'] !== undefined;
                        var isExTea = selectedSet['All Teachers'] !== undefined;
                        var isExAdm = selectedSet['All Admins'] !== undefined;
                        var selCls = Object.keys(selectedSet)
                            .filter(function(k) {
                                return !/^(STU|TEA|ADM|All)/.test(k);
                            })
                            .map(function(k) {
                                return (k.split('|')[0] || '').trim();
                            });

                        if (!data.length) {
                            $results.innerHTML = '<div class="cn-no-results">No matches</div>';
                            $results.style.display = 'block';
                            return;
                        }
                        var html = '';
                        data.forEach(function(item) {
                            if (item.type === 'Student' && (isExStu || selCls.includes(item.class_key || ''))) return;
                            if (item.type === 'Teacher' && isExTea) return;
                            if (item.type === 'Admin' && isExAdm) return;
                            var bc = item.type === 'Admin' ? 'cn-badge-admin' :
                                item.type === 'Teacher' ? 'cn-badge-teacher' : 'cn-badge-student';
                            html += '<div class="cn-result" data-id="' + item.id +
                                '" data-label="' + encodeURIComponent(item.label) + '">' +
                                '<span class="cn-badge ' + bc + '">' + item.type.charAt(0) + '</span>' +
                                item.label + '</div>';
                        });
                        $results.innerHTML = html || '<div class="cn-no-results">No valid matches</div>';
                        $results.style.display = 'block';
                    })
                    .catch(function() {
                        $results.innerHTML = '<div class="cn-no-results" style="color:var(--rose)">Search failed</div>';
                        $results.style.display = 'block';
                    });
            }, 280);
        });

        $results.addEventListener('click', function(e) {
            var item = e.target.closest('.cn-result');
            if (!item) return;
            addTag(item.dataset.id, decodeURIComponent(item.dataset.label));
            $results.style.display = 'none';
            $results.innerHTML = '';
            $search.value = '';
        });
        document.addEventListener('click', function(e) {
            if (!$search.contains(e.target) && !$results.contains(e.target)) $results.style.display = 'none';
        });

        $form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!Object.keys(selectedSet).length) {
                toast('Please select at least one recipient.', 'error');
                return;
            }
            sync();
            $submit.disabled = true;
            $submit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending…';
            fetch($form.action, {
                    method: 'POST',
                    body: new FormData($form)
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(resp) {
                    if (resp.status === 'success') {
                        toast('Notice sent successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1200);
                    } else {
                        toast(resp.message || 'Failed to send.', 'error');
                        $submit.disabled = false;
                        $submit.innerHTML = '<i class="fa fa-paper-plane"></i> Send Notice';
                    }
                })
                .catch(function(err) {
                    toast('Network error: ' + err.message, 'error');
                    $submit.disabled = false;
                    $submit.innerHTML = '<i class="fa fa-paper-plane"></i> Send Notice';
                });
        });

        function loadRecent() {
            fetch(SITE + '/NoticeAnnouncement/fetch_recent_notices')
                .then(function(r) {
                    return r.json();
                })
                .then(function(notices) {
                    var el = document.getElementById('cnRecentList');
                    if (!notices || !notices.length) {
                        el.innerHTML = '<p style="font-size:12px;color:var(--muted);text-align:center;padding:10px 0">No notices yet</p>';
                        return;
                    }
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);
                    var weekAgo = new Date(today);
                    weekAgo.setDate(weekAgo.getDate() - 7);
                    var todayCnt = 0,
                        weekCnt = 0,
                        html = '';
                    notices.forEach(function(n) {
                        var ts = n.Time_Stamp || n.Timestamp || 0;
                        var d = ts ? new Date(ts) : new Date();
                        var str = d.toLocaleDateString('en-IN', {
                            day: 'numeric',
                            month: 'short'
                        });
                        var keys = n['To Id'] ? Object.keys(n['To Id']) : [];
                        var rec = keys.length > 1 ? keys.length + ' recipients' : keys.length === 1 ? keys[0] : 'All';
                        if (d >= today) todayCnt++;
                        if (d >= weekAgo) weekCnt++;
                        html += '<div class="cn-notice-item">' +
                            '<div class="cn-notice-title">' + (n.Title || 'Untitled') + '</div>' +
                            '<div class="cn-notice-meta">' +
                            '<span class="cn-notice-time"><i class="fa fa-clock-o"></i> ' + str + '</span>' +
                            '<span class="cn-notice-recip">' + rec + '</span>' +
                            '</div></div>';
                    });
                    el.innerHTML = html;
                    var st = document.getElementById('statToday');
                    if (st) st.textContent = todayCnt;
                    var sw = document.getElementById('statWeek');
                    if (sw) sw.textContent = weekCnt;
                })
                .catch(function() {
                    document.getElementById('cnRecentList').innerHTML =
                        '<p style="font-size:12px;color:var(--rose);text-align:center">Could not load</p>';
                });
        }

        loadRecent();
        updateControls();
    })();
</script>

<style>
.cn-wrap {
  --gold:      #F5AF00;
  --gold-dim:  #c98e00;
  --gold-bg:   rgba(245,175,0,0.07);
  --gold-ring: rgba(245,175,0,0.25);
  --green:     #3dd68c;
  --blue:      #4ab5e3;
  --rose:      #e05c6f;
  --border:    rgba(0,0,0,0.09);
  --muted:     #6b7593;
  --r:         10px;
}
.cn-wrap { padding: 26px; }
.cn-page-head {
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:22px;flex-wrap:wrap;gap:10px;
}
.cn-page-head h2 {
  font-family:'Syne',sans-serif;font-size:22px;font-weight:800;
  color:#0d1117;margin:0;display:flex;align-items:center;gap:9px;
}
.cn-page-head h2 i { color:var(--gold); }
.cn-back-btn {
  display:inline-flex;align-items:center;gap:6px;
  padding:7px 15px;border-radius:8px;border:1px solid var(--border);
  background:#fff;color:var(--muted);font-size:12px;font-weight:600;
  text-decoration:none;transition:all .18s;
}
.cn-back-btn:hover { border-color:var(--gold);color:var(--gold); }
.cn-grid { display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start; }
@media(max-width:992px){ .cn-grid { grid-template-columns:1fr; } }
.cn-card {
  background:#fff;border:1px solid var(--border);
  border-radius:13px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;
}
.cn-card-head {
  padding:15px 20px 13px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:10px;
}
.cn-card-icon {
  width:32px;height:32px;border-radius:8px;background:var(--gold-bg);color:var(--gold);
  display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;
}
.cn-card-head-text h4 { font-size:14px;font-weight:700;color:#0d1117;margin:0; }
.cn-card-head-text p  { font-size:11px;color:var(--muted);margin:0; }
.cn-card-body { padding:20px; }
.cn-label {
  display:block;font-size:11px;font-weight:700;color:var(--muted);
  text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;
}
.cn-label .req { color:var(--rose); }
.cn-input,.cn-textarea,.cn-select {
  width:100%;border:1px solid rgba(0,0,0,.13);border-radius:var(--r);
  padding:10px 13px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;
  background:#fafafa;color:#1a1f36;outline:none;
  transition:border-color .18s,box-shadow .18s;box-sizing:border-box;
}
.cn-input:focus,.cn-textarea:focus,.cn-select:focus {
  border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-ring);background:#fff;
}
.cn-textarea { resize:vertical;min-height:100px; }
.cn-select {
  appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7593' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 13px center;
  padding-right:34px;cursor:pointer;
}
.cn-field { margin-bottom:16px; }
.cn-recip-row { display:grid;grid-template-columns:repeat(3,1fr);gap:14px; }
@media(max-width:768px){ .cn-recip-row { grid-template-columns:1fr; } }
.cn-check-group {
  background:#f7f8fc;border:1px solid var(--border);
  border-radius:var(--r);padding:13px;
}
.cn-group-title {
  font-size:10.5px;font-weight:700;text-transform:uppercase;
  letter-spacing:.7px;color:var(--muted);margin-bottom:9px;
}
.cn-check-row {
  display:flex;align-items:center;gap:8px;padding:6px 0;
  border-bottom:1px solid rgba(0,0,0,.06);cursor:pointer;
}
.cn-check-row:last-child { border-bottom:none; }
.cn-check-row input[type=checkbox] { width:15px;height:15px;accent-color:var(--gold);cursor:pointer; }
.cn-check-row label { font-size:12.5px;color:#444;cursor:pointer;flex:1; }
.cn-check-row:hover label { color:#111; }
.cn-search-wrap { position:relative; }
.cn-search-icon {
  position:absolute;left:11px;top:50%;transform:translateY(-50%);
  color:var(--muted);font-size:12px;pointer-events:none;
}
.cn-search-wrap .cn-input { padding-left:32px; }
#cnSearchResults {
  display:none;position:absolute;left:0;right:0;top:calc(100% + 5px);
  background:#fff;border:1px solid rgba(0,0,0,.12);border-radius:10px;
  max-height:200px;overflow-y:auto;z-index:999;
  box-shadow:0 6px 24px rgba(0,0,0,.10);
}
.cn-result {
  padding:9px 13px;font-size:12.5px;color:#444;cursor:pointer;
  border-bottom:1px solid rgba(0,0,0,.05);display:flex;align-items:center;gap:8px;
  transition:background .13s;
}
.cn-result:last-child { border-bottom:none; }
.cn-result:hover { background:var(--gold-bg);color:#111; }
.cn-badge { font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;flex-shrink:0; }
.cn-badge-admin   { background:rgba(245,175,0,.15);color:var(--gold-dim); }
.cn-badge-teacher { background:rgba(74,181,227,.15);color:#2a8fbf; }
.cn-badge-student { background:rgba(61,214,140,.15);color:#1fa86a; }
.cn-no-results { padding:11px 13px;font-size:12px;color:var(--muted);text-align:center; }
.cn-tags-wrap {
  display:none;margin-top:16px;padding:13px;
  background:#f7f8fc;border:1px solid var(--border);border-radius:var(--r);
}
.cn-tags-title {
  font-size:10.5px;font-weight:700;text-transform:uppercase;
  letter-spacing:.6px;color:var(--muted);margin-bottom:9px;
}
.cn-tags-list { display:flex;flex-wrap:wrap;gap:7px; }
.cn-tag {
  display:inline-flex;align-items:center;gap:6px;background:#fff;
  border:1px solid rgba(0,0,0,.12);border-radius:50px;padding:4px 10px 4px 11px;
  font-size:12px;color:#333;animation:cnTagIn .18s ease;
}
@keyframes cnTagIn{from{opacity:0;transform:scale(.85)}to{opacity:1;transform:scale(1)}}
.cn-tag-dot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
.cn-tag-remove {
  background:rgba(224,92,111,.12);color:var(--rose);border:none;
  border-radius:50%;width:17px;height:17px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:10px;transition:background .15s;
}
.cn-tag-remove:hover { background:rgba(224,92,111,.28); }
.cn-divider { height:1px;background:var(--border);margin:18px 0; }
.cn-submit {
  width:100%;background:var(--gold);color:#0d1117;border:none;
  border-radius:var(--r);padding:12px 20px;font-size:14px;font-weight:700;
  font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:7px;
  transition:background .18s,box-shadow .18s,transform .14s;margin-top:20px;
}
.cn-submit:hover {
  background:var(--gold-dim);box-shadow:0 5px 18px rgba(245,175,0,.35);
  transform:translateY(-1px);
}
.cn-submit:disabled { opacity:.55;cursor:not-allowed;transform:none; }
.cn-notice-item { padding:12px 0;border-bottom:1px solid var(--border); }
.cn-notice-item:last-child { border-bottom:none; }
.cn-notice-title {
  font-size:13px;font-weight:600;color:#1a1f36;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.cn-notice-meta { display:flex;align-items:center;gap:8px;margin-top:4px; }
.cn-notice-time { font-size:11px;color:var(--muted); }
.cn-notice-recip {
  font-size:10px;padding:2px 7px;border-radius:4px;
  background:var(--gold-bg);color:var(--gold-dim);font-weight:600;
}
.cn-stat-row {
  display:flex;justify-content:space-between;align-items:center;
  padding:10px 0;border-bottom:1px solid var(--border);
}
.cn-stat-row:last-child { border-bottom:none; }
.cn-stat-label { font-size:12px;color:var(--muted); }
.cn-stat-val {
  font-size:18px;font-weight:800;
  font-family:'JetBrains Mono',monospace;
}
#cnToastWrap {
  position:fixed;bottom:24px;right:24px;
  display:flex;flex-direction:column;gap:8px;z-index:9999;pointer-events:none;
}
.cn-toast {
  display:flex;align-items:center;gap:9px;padding:11px 16px;border-radius:10px;
  background:#fff;border:1px solid rgba(0,0,0,.1);
  box-shadow:0 6px 24px rgba(0,0,0,.12);font-size:13px;color:#1a1f36;
  pointer-events:all;animation:cnToastIn .28s ease;
}
@keyframes cnToastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.cn-toast.hide { opacity:0;transform:translateY(8px);transition:all .25s; }
.cn-toast-success i { color:var(--green); }
.cn-toast-error   i { color:var(--rose); }
</style>