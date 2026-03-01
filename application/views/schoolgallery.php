<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
<div class="sg-wrap">

    <!-- ── TOP BAR ── -->
    <div class="sg-topbar">
        <div>
            <h1 class="sg-page-title"><i class="fa fa-picture-o"></i> School Gallery</h1>
            <ol class="sg-breadcrumb">
                <li><a href="<?= base_url() ?>"><i class="fa fa-home"></i> Dashboard</a></li>
                <li>Schools</li>
                <li>Gallery</li>
            </ol>
        </div>
        <div class="sg-topbar-right">
            <button class="sg-btn sg-btn-primary" onclick="document.getElementById('sgFileInput').click()">
                <i class="fa fa-cloud-upload"></i> Upload Media
            </button>
            <input type="file" id="sgFileInput" multiple accept="image/*,video/*" style="display:none;">
        </div>
    </div>

    <!-- ── STAT STRIP ── -->
    <div class="sg-stat-strip">
        <div class="sg-stat sg-stat-blue">
            <div class="sg-stat-icon"><i class="fa fa-picture-o"></i></div>
            <div>
                <div class="sg-stat-label">Total Images</div>
                <div class="sg-stat-val" id="statImages">0</div>
            </div>
        </div>
        <div class="sg-stat sg-stat-amber">
            <div class="sg-stat-icon"><i class="fa fa-film"></i></div>
            <div>
                <div class="sg-stat-label">Total Videos</div>
                <div class="sg-stat-val" id="statVideos">0</div>
            </div>
        </div>
        <div class="sg-stat sg-stat-green">
            <div class="sg-stat-icon"><i class="fa fa-th-large"></i></div>
            <div>
                <div class="sg-stat-label">Total Media</div>
                <div class="sg-stat-val" id="statTotal">0</div>
            </div>
        </div>
    </div>

    <!-- ── UPLOAD ZONE ── -->
    <div class="sg-card">
        <div class="sg-card-head">
            <div class="sg-card-head-left">
                <i class="fa fa-cloud-upload"></i>
                <h3>Upload Media</h3>
            </div>
        </div>
        <div class="sg-card-body">
            <div class="sg-upload-zone" id="sgDropZone" onclick="document.getElementById('sgFileInput').click()">
                <div class="sg-upload-icon"><i class="fa fa-cloud-upload"></i></div>
                <p style="font-size:14px;font-weight:600;color:#1a2332;margin:0 0 4px;">
                    Drop images &amp; videos here or click to browse
                </p>
                <p class="sg-upload-hint">Images: JPG, PNG, WEBP (max 5MB) &nbsp;·&nbsp; Videos: MP4, MOV, AVI (max 50MB)</p>
            </div>
            <div class="sg-upload-progress" id="sgUploadProgress">
                <div class="sg-upload-bar" id="sgUploadBar"></div>
            </div>
            <div class="sg-upload-status" id="sgUploadStatus"></div>
        </div>
    </div>

    <!-- ── GALLERY CARD ── -->
    <div class="sg-card">
        <div class="sg-card-head">
            <div class="sg-card-head-left">
                <i class="fa fa-th"></i>
                <h3>Media Library</h3>
            </div>
            <div class="sg-card-head-right">
                <button class="sg-btn sg-btn-danger sg-btn-sm" id="sgDeleteSelBtn" style="display:none;"
                    onclick="deleteSelectedFiles()">
                    <i class="fa fa-trash-o"></i> Delete Selected (<span id="sgSelCount">0</span>)
                </button>
            </div>
        </div>

        <div class="sg-card-body">

            <!-- Filter bar -->
            <div class="sg-filter-bar">
                <div class="sg-search-wrap">
                    <i class="fa fa-search sg-search-icon"></i>
                    <input type="text" class="sg-search" id="sgSearch" placeholder="Search by filename or date…" autocomplete="off">
                </div>
                <div class="sg-tab-pills">
                    <button class="sg-tab active" data-tab="all">All</button>
                    <button class="sg-tab" data-tab="images">Images</button>
                    <button class="sg-tab" data-tab="videos">Videos</button>
                </div>
                <span class="sg-media-count" id="sgMediaCount">Loading…</span>
            </div>

            <!-- Gallery content -->
            <div id="sgGalleryContent">
                <!-- Images section -->
                <div class="sg-category" id="sgImagesSection">
                    <div class="sg-section-title">
                        <i class="fa fa-picture-o" style="color:var(--sg-teal);"></i>
                        Images
                        <span class="sg-section-toggle" onclick="toggleSection('sgImagesGrid', 'sgImgArrow')">
                            <i class="fa fa-chevron-up" id="sgImgArrow"></i>
                        </span>
                    </div>
                    <div class="sg-grid" id="sgImagesGrid"></div>
                </div>

                <!-- Videos section -->
                <div class="sg-category" id="sgVideosSection">
                    <div class="sg-section-title">
                        <i class="fa fa-film" style="color:var(--sg-amber);"></i>
                        Videos
                        <span class="sg-section-toggle" onclick="toggleSection('sgVideosGrid', 'sgVidArrow')">
                            <i class="fa fa-chevron-up" id="sgVidArrow"></i>
                        </span>
                    </div>
                    <div class="sg-grid" id="sgVideosGrid"></div>
                </div>

                <!-- Loading / empty state -->
                <div class="sg-empty" id="sgEmptyState" style="display:none;">
                    <i class="fa fa-picture-o"></i>
                    <p style="font-size:15px;font-weight:600;margin:0 0 6px;">No media uploaded yet</p>
                    <p style="font-size:13px;margin:0 0 16px;">Upload images and videos to build your school gallery.</p>
                    <button class="sg-btn sg-btn-primary" onclick="document.getElementById('sgFileInput').click()">
                        <i class="fa fa-cloud-upload"></i> Upload Now
                    </button>
                </div>
            </div>

        </div>
    </div>

</div><!-- /.sg-wrap -->
</div><!-- /.content-wrapper -->


<!-- ── Lightbox ── -->
<div class="sg-lightbox" id="sgLightbox">
    <div class="sg-lightbox-inner" id="sgLightboxInner">
        <button class="sg-lightbox-close" onclick="closeLightbox()">&times;</button>
    </div>
</div>

<!-- ── Loading overlay ── -->
<div class="sg-loading" id="sgLoading">
    <div class="sg-spinner"></div>
</div>

<!-- Toast -->
<div class="sg-toast-wrap" id="sgToastWrap"></div>


<script>
/* ── State ── */
var SG = { images: [], videos: [], activeTab: 'all' };

var BASE = '<?= rtrim(base_url(), '/') ?>';

/* ── Utilities ── */
function showToast(msg, type) {
    var wrap = document.getElementById('sgToastWrap');
    var el = document.createElement('div');
    el.className = 'sg-toast sg-toast-' + (type || 'info');
    var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
    el.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
    wrap.appendChild(el);
    setTimeout(function() {
        el.classList.add('sg-toast-hide');
        setTimeout(function() { el.remove(); }, 350);
    }, 3500);
}

function showLoading(v) {
    document.getElementById('sgLoading').classList[v ? 'add' : 'remove']('active');
}

function extractFileName(url) {
    try {
        var dec = decodeURIComponent(url);
        var parts = dec.split('/');
        return parts[parts.length - 1].split('?')[0];
    } catch (e) { return 'Unknown'; }
}

function formatDate(ts) {
    return new Date(ts * 1000).toLocaleDateString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

/* ── Toggle section ── */
function toggleSection(gridId, arrowId) {
    var grid  = document.getElementById(gridId);
    var arrow = document.getElementById(arrowId);
    if (!grid) return;
    var collapsed = grid.style.display === 'none';
    grid.style.display = collapsed ? 'grid' : 'none';
    arrow.className = collapsed ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
}

/* ── Render media ── */
function renderAll() {
    renderGrid(SG.images, 'sgImagesGrid', 'image');
    renderGrid(SG.videos, 'sgVideosGrid', 'video');

    document.getElementById('statImages').textContent = SG.images.length;
    document.getElementById('statVideos').textContent = SG.videos.length;
    document.getElementById('statTotal').textContent  = SG.images.length + SG.videos.length;

    updateMediaCount();
    applyTabFilter(SG.activeTab);

    var empty = SG.images.length === 0 && SG.videos.length === 0;
    document.getElementById('sgEmptyState').style.display   = empty ? 'block' : 'none';
    document.getElementById('sgImagesSection').style.display = empty ? 'none' : '';
    document.getElementById('sgVideosSection').style.display = empty ? 'none' : '';
}

function renderGrid(items, gridId, type) {
    var grid = document.getElementById(gridId);
    grid.innerHTML = '';

    if (!items.length) {
        grid.innerHTML = '<p style="font-size:13px;color:var(--sg-muted);padding:16px 0;">No ' + type + 's found.</p>';
        return;
    }

    items.forEach(function(media) {
        var name = extractFileName(media.url);
        var date = formatDate(media.timestamp);
        var safeUrl = media.url.replace(/'/g, "\\'");

        var mediaHtml = '';
        if (type === 'image') {
            mediaHtml = '<img src="' + media.url + '" class="sg-item-media" alt="' + name + '" onclick="openLightbox(\'' + safeUrl + '\',\'image\')">';
        } else {
            var thumb    = media.thumbnail || media.url;
            var duration = media.duration || '';
            var safeThumb = thumb.replace(/'/g, "\\'");
            mediaHtml = '<div class="sg-item-video-wrap" onclick="openLightbox(\'' + safeUrl + '\',\'video\')">'
                + '<img src="' + thumb + '" alt="' + name + '">'
                + '<div class="sg-play-btn"><i class="fa fa-play-circle"></i></div>'
                + (duration ? '<span class="sg-duration">' + duration + '</span>' : '')
                + '</div>';
        }

        var item = document.createElement('div');
        item.className = 'sg-item';
        item.dataset.url  = media.url;
        item.dataset.name = name.toLowerCase();
        item.dataset.date = date.toLowerCase();
        item.dataset.type = type;

        item.innerHTML =
            '<input type="checkbox" class="sg-item-check" onclick="handleCheck(event, this)" data-url="' + media.url + '">' +
            mediaHtml +
            '<div class="sg-item-footer">' +
                '<div class="sg-item-name" title="' + name + '">' + name + '</div>' +
                '<div class="sg-item-date">' + date + '</div>' +
                '<div class="sg-item-actions">' +
                    '<button class="sg-btn sg-btn-ghost sg-btn-sm" onclick="openLightbox(\'' + safeUrl + '\',\'' + type + '\')">' +
                        '<i class="fa fa-eye"></i></button>' +
                    '<button class="sg-btn sg-btn-danger sg-btn-sm" onclick="deleteFile(\'' + safeUrl + '\', this)">' +
                        '<i class="fa fa-trash-o"></i></button>' +
                '</div>' +
            '</div>';

        grid.appendChild(item);
    });
}

/* ── Fetch gallery ── */
function fetchGalleryMedia() {
    showLoading(true);
    document.getElementById('sgMediaCount').textContent = 'Loading…';

    fetch(BASE + '/schools/fetchGalleryMedia')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            SG.images = data.images || [];
            SG.videos = data.videos || [];
            renderAll();
        })
        .catch(function(e) {
            console.error('fetchGallery:', e);
            showToast('Failed to load gallery.', 'error');
        })
        .finally(function() { showLoading(false); });
}

/* ── Search ── */
document.getElementById('sgSearch').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    var items = document.querySelectorAll('.sg-item');
    var shown = 0;
    items.forEach(function(item) {
        var match = !q || item.dataset.name.includes(q) || item.dataset.date.includes(q);
        var tabOk = SG.activeTab === 'all' || item.dataset.type === SG.activeTab.replace('s','');
        item.style.display = (match && tabOk) ? '' : 'none';
        if (match && tabOk) shown++;
    });
    document.getElementById('sgMediaCount').textContent = shown + ' item(s)';
});

/* ── Tab filter ── */
document.querySelectorAll('.sg-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.sg-tab').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');
        SG.activeTab = this.dataset.tab;
        applyTabFilter(SG.activeTab);
    });
});

function applyTabFilter(tab) {
    var imgSec = document.getElementById('sgImagesSection');
    var vidSec = document.getElementById('sgVideosSection');
    if (tab === 'all')    { imgSec.style.display = ''; vidSec.style.display = ''; }
    if (tab === 'images') { imgSec.style.display = ''; vidSec.style.display = 'none'; }
    if (tab === 'videos') { imgSec.style.display = 'none'; vidSec.style.display = ''; }
    updateMediaCount();
}

function updateMediaCount() {
    var total = SG.activeTab === 'images' ? SG.images.length
              : SG.activeTab === 'videos' ? SG.videos.length
              : SG.images.length + SG.videos.length;
    document.getElementById('sgMediaCount').textContent = total + ' item(s)';
}

/* ── Checkbox selection ── */
function handleCheck(e, cb) {
    e.stopPropagation();
    var item = cb.closest('.sg-item');
    if (cb.checked) item.classList.add('selected');
    else            item.classList.remove('selected');
    updateSelCount();
}

function updateSelCount() {
    var count  = document.querySelectorAll('.sg-item-check:checked').length;
    var btn    = document.getElementById('sgDeleteSelBtn');
    var span   = document.getElementById('sgSelCount');
    span.textContent = count;
    btn.style.display = count > 0 ? 'inline-flex' : 'none';
}

function deleteSelectedFiles() {
    var cbs = document.querySelectorAll('.sg-item-check:checked');
    if (!cbs.length) return;
    if (!confirm('Delete ' + cbs.length + ' selected file(s)? This cannot be undone.')) return;

    var promises = Array.from(cbs).map(function(cb) {
        return deleteFilePromise(cb.dataset.url);
    });

    Promise.all(promises).then(function() {
        showToast('Selected files deleted.', 'success');
        fetchGalleryMedia();
        updateSelCount();
    });
}

/* ── Delete ── */
function deleteFile(fileUrl, btnEl) {
    if (!confirm('Delete this file permanently?')) return;

    var item = btnEl.closest('.sg-item');
    btnEl.disabled = true;

    deleteFilePromise(fileUrl).then(function(ok) {
        if (ok) {
            item.style.transition = 'all .3s';
            item.style.opacity = '0';
            item.style.transform = 'scale(.9)';
            setTimeout(function() {
                item.remove();
                // Refresh counts
                SG.images = SG.images.filter(function(m) { return m.url !== fileUrl; });
                SG.videos = SG.videos.filter(function(m) { return m.url !== fileUrl; });
                document.getElementById('statImages').textContent = SG.images.length;
                document.getElementById('statVideos').textContent = SG.videos.length;
                document.getElementById('statTotal').textContent  = SG.images.length + SG.videos.length;
                updateMediaCount();
                updateSelCount();
            }, 300);
            showToast('File deleted.', 'success');
        }
    });
}

function deleteFilePromise(fileUrl) {
    return fetch(BASE + '/schools/deleteMedia?url=' + encodeURIComponent(fileUrl), { method: 'DELETE' })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.status !== 'success') { showToast('Delete failed: ' + d.message, 'error'); return false; }
            return true;
        })
        .catch(function(e) { console.error('deleteFile:', e); showToast('Delete error.', 'error'); return false; });
}

/* ── Lightbox ── */
function openLightbox(url, type) {
    var lb     = document.getElementById('sgLightbox');
    var inner  = document.getElementById('sgLightboxInner');
    var btnHtml = '<button class="sg-lightbox-close" onclick="closeLightbox()">&times;</button>';

    if (type === 'image') {
        inner.innerHTML = btnHtml + '<img src="' + url + '" alt="Preview">';
    } else {
        inner.innerHTML = btnHtml + '<video controls autoplay style="max-width:90vw;max-height:85vh;border-radius:8px;background:#000;">' +
            '<source src="' + url + '" type="video/mp4">Your browser does not support video.</video>';
    }

    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    var lb = document.getElementById('sgLightbox');
    lb.classList.remove('open');
    document.body.style.overflow = 'auto';
    setTimeout(function() { document.getElementById('sgLightboxInner').innerHTML = ''; }, 300);
}

document.getElementById('sgLightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});

/* ── Drag & drop ── */
var dropZone = document.getElementById('sgDropZone');
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', function()  { this.classList.remove('drag-over'); });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault(); this.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
});

/* ── File input ── */
document.getElementById('sgFileInput').addEventListener('change', function(e) {
    handleFiles(e.target.files);
    this.value = '';
});

/* ── Upload ── */
function handleFiles(files) {
    if (!files.length) return;

    var validFiles = [];
    Array.from(files).forEach(function(f) {
        var ext = f.name.split('.').pop().toLowerCase();
        var isImg = ['jpg','jpeg','png','webp'].includes(ext);
        var isVid = ['mp4','mov','avi','mkv','webm'].includes(ext);
        if (!isImg && !isVid) {
            showToast('Skipped ' + f.name + ' — unsupported format.', 'error');
            return;
        }
        if (isImg && f.size > 5*1024*1024) {
            showToast(f.name + ' exceeds 5MB limit.', 'error'); return;
        }
        if (isVid && f.size > 50*1024*1024) {
            showToast(f.name + ' exceeds 50MB limit.', 'error'); return;
        }
        validFiles.push(f);
    });

    if (!validFiles.length) return;

    var progress  = document.getElementById('sgUploadProgress');
    var bar       = document.getElementById('sgUploadBar');
    var statusEl  = document.getElementById('sgUploadStatus');
    progress.style.display = 'block';
    statusEl.style.display = 'block';
    bar.style.width = '0%';

    var total   = validFiles.length;
    var done    = 0;
    var failed  = 0;

    function next(i) {
        if (i >= total) {
            bar.style.width = '100%';
            statusEl.textContent = failed ? (total - failed) + ' uploaded, ' + failed + ' failed.' : total + ' file(s) uploaded successfully!';
            statusEl.style.color = failed ? 'var(--sg-red)' : 'var(--sg-green)';
            if (!failed) showToast(total + ' file(s) uploaded!', 'success');
            setTimeout(function() {
                progress.style.display = 'none';
                statusEl.style.display = 'none';
                bar.style.width = '0%';
                fetchGalleryMedia();
            }, 1800);
            return;
        }

        var file = validFiles[i];
        var type = file.type.startsWith('image') ? '1' : '2';
        var fd   = new FormData();
        fd.append('file', file);
        fd.append('type', type);

        statusEl.textContent = 'Uploading ' + (i+1) + ' of ' + total + ': ' + file.name;
        statusEl.style.color = 'var(--sg-teal)';

        fetch(BASE + '/schools/uploadMedia', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.status !== 'success') { failed++; showToast('Failed: ' + file.name, 'error'); }
            })
            .catch(function() { failed++; })
            .finally(function() {
                done++;
                bar.style.width = Math.round((done / total) * 100) + '%';
                next(i + 1);
            });
    }

    next(0);
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', function() {
    fetchGalleryMedia();
});
</script>

<style>
/* ── School Gallery — matches ERP theme ── */
:root {
    --sg-navy:   #1a2332;
    --sg-teal:   #0d9488;
    --sg-teal-lt:#ccfbf1;
    --sg-amber:  #d97706;
    --sg-red:    #dc2626;
    --sg-green:  #16a34a;
    --sg-muted:  #6b7280;
    --sg-border: #e5e7eb;
    --sg-bg:     #f4f6f9;
    --sg-white:  #ffffff;
    --sg-shadow: 0 2px 8px rgba(0,0,0,.08);
    --sg-radius: 10px;
}

.sg-wrap { padding: 20px 24px; background: var(--sg-bg); min-height: 100vh; }

/* ── Top bar ── */
.sg-topbar {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 22px; flex-wrap: wrap; gap: 12px;
}
.sg-page-title { font-size: 22px; font-weight: 700; color: var(--sg-navy); margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
.sg-page-title i { color: var(--sg-teal); }
.sg-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--sg-muted); }
.sg-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; }
.sg-breadcrumb a { color: var(--sg-teal); text-decoration: none; }
.sg-topbar-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

/* ── Buttons ── */
.sg-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 7px; font-size: 13px; font-weight: 600;
    cursor: pointer; border: none; transition: all .18s; text-decoration: none;
}
.sg-btn-primary { background: var(--sg-teal);  color: #fff; }
.sg-btn-primary:hover { background: #0f766e; }
.sg-btn-danger  { background: var(--sg-red);   color: #fff; }
.sg-btn-danger:hover  { background: #b91c1c; }
.sg-btn-ghost   { background: #fff; color: var(--sg-navy); border: 1.5px solid var(--sg-border); }
.sg-btn-ghost:hover   { border-color: var(--sg-teal); color: var(--sg-teal); }
.sg-btn-amber   { background: var(--sg-amber); color: #fff; }
.sg-btn-amber:hover   { background: #b45309; }
.sg-btn-sm { padding: 6px 12px; font-size: 12px; }
.sg-btn:disabled { opacity: .55; cursor: not-allowed; }

/* ── Stat strip ── */
.sg-stat-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 22px; }
.sg-stat { background: var(--sg-white); border-radius: var(--sg-radius); padding: 16px 18px; display: flex; align-items: center; gap: 14px; box-shadow: var(--sg-shadow); border-left: 4px solid transparent; }
.sg-stat-blue  { border-left-color: #3b82f6; }
.sg-stat-green { border-left-color: var(--sg-green); }
.sg-stat-amber { border-left-color: var(--sg-amber); }
.sg-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.sg-stat-blue  .sg-stat-icon { background: #eff6ff; color: #3b82f6; }
.sg-stat-green .sg-stat-icon { background: #f0fdf4; color: var(--sg-green); }
.sg-stat-amber .sg-stat-icon { background: #fffbeb; color: var(--sg-amber); }
.sg-stat-label { font-size: 11px; color: var(--sg-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
.sg-stat-val   { font-size: 22px; font-weight: 800; color: var(--sg-navy); line-height: 1.2; }

/* ── Card ── */
.sg-card { background: var(--sg-white); border-radius: var(--sg-radius); box-shadow: var(--sg-shadow); margin-bottom: 22px; overflow: hidden; }
.sg-card-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1.5px solid var(--sg-border); flex-wrap: wrap; gap: 10px; }
.sg-card-head-left { display: flex; align-items: center; gap: 10px; }
.sg-card-head h3 { margin: 0; font-size: 15px; font-weight: 700; color: var(--sg-navy); }
.sg-card-head i  { color: var(--sg-teal); font-size: 16px; }
.sg-card-head-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.sg-card-body { padding: 20px; }

/* ── Upload zone ── */
.sg-upload-zone {
    border: 2px dashed var(--sg-border); border-radius: var(--sg-radius);
    padding: 32px 20px; text-align: center; cursor: pointer;
    transition: all .2s; background: #fafafa; margin-bottom: 20px;
}
.sg-upload-zone:hover, .sg-upload-zone.drag-over {
    border-color: var(--sg-teal); background: var(--sg-teal-lt);
}
.sg-upload-icon { font-size: 36px; color: var(--sg-teal); margin-bottom: 10px; }
.sg-upload-hint { font-size: 13px; color: var(--sg-muted); margin: 4px 0 0; }
.sg-upload-progress {
    display: none; margin-top: 14px;
    background: #e5e7eb; border-radius: 20px; height: 6px; overflow: hidden;
}
.sg-upload-bar { height: 100%; background: var(--sg-teal); width: 0%; transition: width .3s; }
.sg-upload-status { font-size: 12px; color: var(--sg-teal); margin-top: 8px; display: none; }

/* ── Filter / search ── */
.sg-filter-bar { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
.sg-search-wrap { position: relative; flex: 1; min-width: 180px; }
.sg-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--sg-muted); font-size: 13px; }
.sg-search { width: 100%; padding: 8px 12px 8px 32px; border: 1.5px solid var(--sg-border); border-radius: 7px; font-size: 13px; outline: none; transition: border .18s; }
.sg-search:focus { border-color: var(--sg-teal); }
.sg-tab-pills { display: flex; gap: 4px; background: #f3f4f6; border-radius: 8px; padding: 3px; }
.sg-tab { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; background: transparent; color: var(--sg-muted); transition: all .18s; }
.sg-tab.active { background: var(--sg-white); color: var(--sg-navy); box-shadow: 0 1px 4px rgba(0,0,0,.1); }
.sg-media-count { font-size: 12px; color: var(--sg-muted); white-space: nowrap; }

/* ── Selection bar ── */
.sg-selection-bar {
    display: none; align-items: center; justify-content: space-between;
    padding: 10px 16px; background: var(--sg-teal-lt); border-radius: 8px;
    margin-bottom: 14px; border: 1px solid var(--sg-teal);
}
.sg-selection-bar.visible { display: flex; }
.sg-sel-text { font-size: 13px; font-weight: 600; color: var(--sg-teal); }

/* ── Media grid ── */
.sg-section-title {
    font-size: 13px; font-weight: 700; color: var(--sg-navy); margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.sg-section-toggle { cursor: pointer; color: var(--sg-muted); font-size: 12px; margin-left: auto; }

.sg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}

/* ── Grid item ── */
.sg-item {
    border-radius: 10px; overflow: hidden;
    background: var(--sg-white); box-shadow: var(--sg-shadow);
    border: 1.5px solid var(--sg-border); transition: all .18s;
    position: relative;
}
.sg-item:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); border-color: var(--sg-teal); }
.sg-item.selected { border-color: var(--sg-teal); box-shadow: 0 0 0 3px rgba(13,148,136,.2); }

.sg-item-media {
    width: 100%; height: 150px; object-fit: cover; display: block; cursor: pointer;
}
.sg-item-video-wrap {
    position: relative; width: 100%; height: 150px; overflow: hidden; cursor: pointer;
}
.sg-item-video-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sg-play-btn {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,.3);
}
.sg-play-btn i { font-size: 36px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,.4); }
.sg-duration {
    position: absolute; bottom: 6px; right: 8px;
    background: rgba(0,0,0,.7); color: #fff; font-size: 11px; font-weight: 600;
    padding: 2px 6px; border-radius: 4px;
}

.sg-item-check {
    position: absolute; top: 8px; left: 8px; z-index: 2;
    width: 20px; height: 20px; accent-color: var(--sg-teal); cursor: pointer;
    opacity: 0; transition: opacity .15s;
}
.sg-item:hover .sg-item-check,
.sg-item.selected .sg-item-check { opacity: 1; }

.sg-item-footer {
    padding: 10px 12px; border-top: 1px solid var(--sg-border);
}
.sg-item-name { font-size: 11px; font-weight: 600; color: var(--sg-navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
.sg-item-date { font-size: 10px; color: var(--sg-muted); margin-bottom: 8px; }
.sg-item-actions { display: flex; gap: 6px; }
.sg-item-actions .sg-btn { flex: 1; justify-content: center; }

/* ── Empty state ── */
.sg-empty { text-align: center; padding: 48px 20px; color: var(--sg-muted); }
.sg-empty i { font-size: 40px; margin-bottom: 12px; opacity: .4; display: block; }

/* ── Lightbox ── */
.sg-lightbox {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.88); z-index: 2000;
    align-items: center; justify-content: center; padding: 20px;
}
.sg-lightbox.open { display: flex; }
.sg-lightbox-inner { position: relative; max-width: 90vw; max-height: 90vh; }
.sg-lightbox-inner img { max-width: 90vw; max-height: 85vh; border-radius: 8px; display: block; }
.sg-lightbox-inner video { max-width: 90vw; max-height: 85vh; border-radius: 8px; display: block; }
.sg-lightbox-close {
    position: absolute; top: -14px; right: -14px;
    width: 36px; height: 36px; border-radius: 50%; background: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: none; font-size: 18px; color: var(--sg-navy);
    box-shadow: 0 2px 8px rgba(0,0,0,.3); z-index: 10;
}
.sg-lightbox-close:hover { background: var(--sg-red); color: #fff; }

/* ── Loading overlay ── */
.sg-loading {
    display: none; position: fixed; inset: 0; z-index: 1999;
    background: rgba(255,255,255,.6); align-items: center; justify-content: center;
}
.sg-loading.active { display: flex; }
.sg-spinner { width: 44px; height: 44px; border: 4px solid var(--sg-border); border-top-color: var(--sg-teal); border-radius: 50%; animation: sgSpin .7s linear infinite; }
@keyframes sgSpin { to { transform: rotate(360deg); } }

/* ── Toast ── */
.sg-toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.sg-toast { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,.15); animation: sgToastIn .25s ease; min-width: 240px; }
@keyframes sgToastIn { from { transform: translateX(60px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.sg-toast-success { background: #f0fdf4; color: var(--sg-green); border-left: 4px solid var(--sg-green); }
.sg-toast-error   { background: #fef2f2; color: var(--sg-red);   border-left: 4px solid var(--sg-red); }
.sg-toast-info    { background: #f0fdfa; color: var(--sg-teal);  border-left: 4px solid var(--sg-teal); }
.sg-toast-hide    { animation: sgToastOut .3s ease forwards; }
@keyframes sgToastOut { to { transform: translateX(60px); opacity: 0; } }

/* ── Category sections ── */
.sg-category { margin-bottom: 28px; }
</style>

<style>
    body {
        font-family: 'Roboto', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
    }

    .container {
        padding: 2rem;
        max-width: 1200px;
        margin: auto;
    }

    .upload-section {
        margin-bottom: 2rem;
    }

    .upload-btn {
        background-color: #28a745;
        color: white;
        padding: 0.8rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        border: none;
    }

    #searchInput {
        width: 100%;
        padding: 10px;
        margin-bottom: 1rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    .grid-container {
        margin-top: 2rem;
    }

    .category {
        margin-bottom: 1rem;
    }

    .category h4 {
        background: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 16px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .grid-item {
        background: white;
        padding: 1rem;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s;
    }

    .grid-item:hover {
        transform: scale(1.03);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    .grid-item img,
    .grid-item video {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
    }

    .file-info {
        width: 100%;
        text-align: center;
        padding: 10px 5px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 0 0 8px 8px;
        margin-top: 10px;
        font-size: 14px;
    }

    .file-name {
        font-weight: bold;
        margin: 6px 0 2px;
        word-wrap: break-word;
        font-size: 14px;
        color: #333;
    }

    .upload-time {
        font-size: 12px;
        color: #666;
    }

    .button-container {
        margin-top: 10px;
    }

    .view-btn,
    .delete-btn {
        display: inline-block;
        padding: 6px 12px;
        margin: 4px 6px 0 6px;
        border-radius: 4px;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .view-btn {
        background-color: #007bff;
        color: white;
    }

    .view-btn:hover {
        background-color: #0056b3;
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
        display: none;
    }

    .grid-item:hover .delete-btn {
        display: inline-block;
    }

    .delete-btn:hover {
        background-color: #b02a37;
    }

    /* Modal Styling */
    .modal {
        display: flex;
        /* ← ✅ Always use flex */
        justify-content: center;
        align-items: center;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .modal.show {
        opacity: 1;
        pointer-events: all;
    }

    .modal-content {
        background: transparent;
        padding: 0;
        border-radius: 10px;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);
    }

    .modal-content img,
    .modal-content video {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 5px;
        display: block;
        margin: auto;
    }

    /* .modal-content img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 5px;
    }

    .modal-content video {
        width: 100%;
        height: auto;
        max-height: 80vh;
        object-fit: contain;
        background: #000;
        border-radius: 8px;
    } */

    .modal-img {
        /* max-width: 100%;
        max-height: 80vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
        display: block;
        margin: auto; */
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        display: block;
    }



    .close {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 40px;
        color: #ffffff;
        z-index: 1001;
        cursor: pointer;
    }

    #selectionContainer {
        display: none;
        background-color: #f1f1f1;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    #selectedCount {
        font-weight: bold;
        color: #333;
    }

    #deleteSelectedBtn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 8px 15px;
        cursor: pointer;
        border-radius: 5px;
        font-weight: bold;
        transition: 0.3s;
    }

    #deleteSelectedBtn:hover {
        background-color: #c82333;
    }

    .video-thumbnail-wrapper {
        position: relative;
        width: 100%;
    }

    .video-thumb {
        width: 100%;
        height: 180px;
        border-radius: 4px;
        object-fit: cover;
    }

    .video-duration {
        position: absolute;
        bottom: 6px;
        right: 8px;
        background-color: rgba(0, 0, 0, 0.75);
        color: white;
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 3px;
    }

    .media-top {
        position: relative;
        width: 100%;
    }

    .media-top input[type="checkbox"] {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
        transform: scale(1.2);
    }
</style>