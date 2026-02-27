<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
<div class="sm-wrap">

    <!-- ══ TOP BAR ══ -->
    <div class="sm-topbar">
        <div>
            <h1 class="sm-page-title"><i class="fa fa-book"></i> Subject Management</h1>
            <ol class="sm-breadcrumb">
                <li><a href="<?= base_url('admin/index') ?>"><i class="fa fa-home"></i> Dashboard</a></li>
                <!-- <li><a href="<?= site_url('subjects/manage_subjects') ?>">Subjects</a></li> -->
                <li>Manage Subjects</li>
            </ol>
        </div>
        <div class="sm-school-badge">
            <span class="sm-badge-label">School</span>
            <span class="sm-badge-val"><?= htmlspecialchars($selected_school ?? '') ?></span>
        </div>
    </div>

    <!-- ══ MAIN LAYOUT ══ -->
    <div class="sm-layout">

        <!-- ── LEFT: Class + Subjects ── -->
        <div class="sm-left">

            <!-- STEP 1: Select Class -->
            <div class="sm-card">
                <div class="sm-card-head">
                    <span class="sm-step">1</span>
                    <i class="fa fa-graduation-cap"></i>
                    <h3>Select Class</h3>
                </div>
                <div class="sm-card-body">
                    <form id="subject_form" method="post" novalidate>
<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">
                        <div class="sm-grid-2 sm-mb">
                            <div class="sm-field">
                                <label class="sm-label">Class <span class="sm-req">*</span></label>
                                <div class="sm-select-wrap">
                                    <select class="sm-select" id="classSelect" name="class_name" required>
                                        <option value="" disabled selected>— Select Class —</option>
                                        <option>Class PlayGroup</option>
                                        <option>Class Nursery</option>
                                        <option>Class LKG</option>
                                        <option>Class UKG</option>
                                        <option>Class 1st</option>
                                        <option>Class 2nd</option>
                                        <option>Class 3rd</option>
                                        <option>Class 4th</option>
                                        <option>Class 5th</option>
                                        <option>Class 6th</option>
                                        <option>Class 7th</option>
                                        <option>Class 8th</option>
                                        <option>Class 9th</option>
                                        <option>Class 10th</option>
                                        <option>Class 11th</option>
                                        <option>Class 12th</option>
                                    </select>
                                    <i class="fa fa-chevron-down sm-select-arr"></i>
                                </div>
                            </div>

                            <!-- Structure Type (Class 11/12 only) -->
                            <div class="sm-field" id="patternTypeWrapper" style="display:none;">
                                <label class="sm-label">Structure Type <span class="sm-req">*</span></label>
                                <div class="sm-pattern-toggle">
                                    <button type="button" class="sm-pattern-btn" data-pattern="1">
                                        <i class="fa fa-th-large"></i> Section-wise
                                    </button>
                                    <button type="button" class="sm-pattern-btn" data-pattern="2">
                                        <i class="fa fa-random"></i> Stream-wise
                                    </button>
                                    <button type="button" class="sm-pattern-btn" data-pattern="3">
                                        <i class="fa fa-list-ul"></i> Subject-wise
                                    </button>
                                </div>
                                <input type="hidden" name="pattern_type" id="pattern_type_input" value="">
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <!-- STEP 2: Subjects (populated by JS) -->
            <div class="sm-card" id="subjectsCard" style="display:none;">
                <div class="sm-card-head">
                    <span class="sm-step">2</span>
                    <i class="fa fa-list-alt"></i>
                    <h3>Choose Subjects</h3>
                    <span class="sm-head-hint" id="subjectsHint"></span>
                </div>
                <div class="sm-card-body">
                    <!-- Assessment info injected here -->
                    <div class="left-column">
                        <div id="subjects_holder"></div>
                    </div>
                </div>
            </div>

        </div><!-- /.sm-left -->

        <!-- ── RIGHT: Selected Subjects Summary ── -->
        <div class="sm-right">
            <div class="sm-summary-card">
                <div class="sm-summary-head">
                    <i class="fa fa-check-circle"></i> Selected Subjects
                </div>
                <div class="sm-summary-body">

                    <div class="sm-summary-meta">
                        <div class="sm-summary-row">
                            <span>Class</span>
                            <strong id="sumClass">—</strong>
                        </div>
                        <div class="sm-summary-row">
                            <span>Count</span>
                            <strong id="sumCount">0</strong>
                        </div>
                    </div>
                    <div class="sm-summary-divider"></div>

                    <!-- Selected subjects list -->
                    <div id="selected_subjects" class="sm-selected-list"></div>

                    <div class="sm-selected-empty" id="selectedEmpty">
                        <i class="fa fa-hand-o-up"></i>
                        <p>Select a class then click subjects to add them here.</p>
                    </div>

                </div>

                <!-- Actions -->
                <div class="sm-summary-actions">
                    <button type="button" class="sm-btn sm-btn-ghost sm-btn-sm" id="clearSelected">
                        <i class="fa fa-trash"></i> Clear All
                    </button>
                    <button type="button" class="sm-btn sm-btn-submit" id="saveSubjectsBtn">
                        <i class="fa fa-save"></i> Save Subjects
                    </button>
                </div>
            </div>
        </div><!-- /.sm-right -->

    </div><!-- /.sm-layout -->
</div><!-- /.sm-wrap -->
</div><!-- /.content-wrapper -->


<!-- ══ MODAL: Assessment Info ══ -->
<div class="sm-overlay" id="assessmentInfoModal">
    <div class="sm-modal">
        <div class="sm-modal-head">
            <h4><i class="fa fa-info-circle"></i> Assessment Information &amp; Rules</h4>
            <button class="sm-modal-close" id="closeAssessmentModal">&times;</button>
        </div>
        <div class="sm-modal-body" id="assessmentInfoContent"></div>
    </div>
</div>

<!-- Toast container -->
<div id="smToastWrap" class="sm-toast-wrap"></div>

<script>
/* ═══════════════════════════════════════════════════════════════
   SUBJECT MANAGEMENT — JS
   All original logic preserved 100%.
   Only UI structure updated to match system theme.
═══════════════════════════════════════════════════════════════ */

let patternType = 0;

/* ── Toast utility ── */
function smToast(msg, type) {
    var wrap = document.getElementById('smToastWrap');
    var el   = document.createElement('div');
    el.className = 'sm-toast sm-toast-' + (type || 'info');
    var icons = { success:'check-circle', error:'times-circle', warning:'exclamation-triangle', info:'info-circle' };
    el.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
    wrap.appendChild(el);
    setTimeout(function() {
        el.classList.add('sm-toast-hide');
        setTimeout(function() { el.remove(); }, 350);
    }, 3500);
}

/* ── Assessment modal (custom, no Bootstrap dependency) ── */
function showAssessmentModal(html) {
    document.getElementById('assessmentInfoContent').innerHTML = html;
    document.getElementById('assessmentInfoModal').classList.add('open');
}
document.getElementById('closeAssessmentModal').addEventListener('click', function() {
    document.getElementById('assessmentInfoModal').classList.remove('open');
});
document.getElementById('assessmentInfoModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});

document.addEventListener('DOMContentLoaded', function() {

    /* ── Bootstrap modal shim — inside DOMContentLoaded so $ is available ── */
    /* NOTE: Do NOT call .show() — CSS handles display:flex via .open class.  */
    $.fn.modal = function(action) {
        if (action === 'show') {
            var id = this.attr('id');
            if (id === 'assessmentInfoModal') {
                document.getElementById('assessmentInfoModal').classList.add('open');
            }
        } else if (action === 'hide') {
            this.removeClass('open');
        }
        return this;
    };

    /* ── Update summary panel ── */
    function updateSummary() {
        var items  = $('#selected_subjects .selected-subject');
        var count  = items.length;
        var clsVal = $('#classSelect').val() || '—';
        $('#sumClass').text(clsVal);
        $('#sumCount').text(count);
        if (count > 0) { $('#selectedEmpty').hide(); }
        else           { $('#selectedEmpty').show(); }
    }
    window.updateSummary = updateSummary; // expose for renderSubjects callbacks

    window.languageRules = window.languageRules || {};
    window.languageRules.min = parseInt(window.languageRules.min || 1, 10) || 1;
    window.languageRules.max = parseInt(window.languageRules.max || 2, 10) || 2;

    /* ── Class select: fetch subjects ── */
    $('#classSelect').off('change').on('change', function() {
        var selectedClass = $(this).val();
        if (!selectedClass || selectedClass === 'Select Class') {
            clearSubjectsDisplay();
            $('#subjectsCard').hide(); // BUG FIX: hide subjects card when placeholder re-selected
            return;
        }

        // Update summary
        updateSummary();

        // Show/hide pattern type for 11/12
        var cls = parseInt(String(selectedClass).replace(/\D/g, ''), 10);
        if (cls === 11 || cls === 12) {
            $('#patternTypeWrapper').slideDown(150);
        } else {
            $('#patternTypeWrapper').hide();
            patternType = 0;
            $('#pattern_type_input').val('');
            $('.sm-pattern-btn').removeClass('active');
        }

        // Clear previous subject UI
        clearSubjectsDisplay();

        // Show loading in subjects card
        $('#subjectsCard').show();
        $('#subjectsHint').text('Loading…');
        $('.left-column').html('<div class="sm-loading"><i class="fa fa-spinner fa-spin"></i> Loading subjects…</div>');

        $.ajax({
            url: "<?= base_url('subjects/fetch_subjects') ?>",
            method: 'POST',
            data: { class: selectedClass },
            dataType: 'json',
            success: function(response) {
                $('.left-column').html('<div id="subjects_holder"></div>');
                renderSubjects(response);
                $('#subjectsHint').text('Select subjects for this class');
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('.left-column').html('<div class="sm-empty-msg"><i class="fa fa-exclamation-triangle"></i> Failed to load subjects.</div>');
                smToast('Failed to load subjects.', 'error');
            }
        });
    });

    /* ── Pattern type buttons ── */
    $(document).on('click', '.sm-pattern-btn', function() {
        var val = parseInt($(this).data('pattern'), 10) || 0;
        $('.sm-pattern-btn').removeClass('active');
        $(this).addClass('active');
        patternType = val;
        $('#pattern_type_input').val(val);
    });

    /* ── Clear all ── */
    $('#clearSelected').off('click').on('click', function(e) {
        e.preventDefault();
        $('#selected_subjects').empty();
        $('.subject-btn').filter(function() { return !!$(this).attr('data-dropdown-lang'); }).remove();
        $('.subject-btn').removeClass('active').attr('aria-pressed', 'false');
        $('.language-dropdown, .subject-dropdown').each(function() {
            $(this).val('');
            $(this).trigger('change');
        });
        $('.subject-section, .subject-card, .stream-card').removeClass('category-invalid group-warning');
        if (typeof window.updateLanguageGroupWarning === 'function') window.updateLanguageGroupWarning();
        updateSummary();
    });

    /* ── Save button triggers form submit ── */
    $('#saveSubjectsBtn').on('click', function() {
        $('#subject_form').trigger('submit');
    });

    /* ── Form submit ── */
    $('#subject_form').off('submit').on('submit', function(e) {
        e.preventDefault();

        if ($('#patternTypeWrapper').is(':visible') && !patternType) {
            smToast('Please select Structure Type for Class 11/12.', 'warning');
            return;
        }

        var selectedItems = $('#selected_subjects .selected-subject');
        if (!selectedItems.length) {
            smToast('Please select at least one subject.', 'warning');
            return;
        }

        var subjects = [];
        var additionalSubjects = [];

        selectedItems.each(function() {
            var subjectName = $(this).data('subject-name');
            var category    = $(this).data('category');
            var stream      = $(this).data('stream') || 'common';
            if (!subjectName || !category) return;

            var payload = { name: subjectName, category: category, stream: stream };
            if (String(category).toLowerCase() === 'additional') {
                additionalSubjects.push(payload);
            } else {
                subjects.push(payload);
            }
        });

        var className = $('#classSelect').val();
        if (!className) {
            smToast('Please select a class.', 'warning');
            return;
        }

        var formData = {
            school_name:       "<?= htmlspecialchars($selected_school ?? '') ?>",
            class_name:        className,
            section:           'A',
            pattern_type:      patternType || 0,
            subjects:          JSON.stringify(subjects),
            additionalsubjects: JSON.stringify(additionalSubjects)
        };

        var $btn = $('#saveSubjectsBtn');
        $btn.prop('disabled', true).html("<i class='fa fa-spinner fa-spin'></i> Saving…");

        $.ajax({
            url: "<?= base_url('subjects/manage_subjects') ?>",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (String(response).trim() === '1') {
                    smToast('Subjects saved successfully!', 'success');
                    setTimeout(function() { location.reload(); }, 900);
                } else {
                    smToast('Error saving subjects.', 'error');
                    $btn.prop('disabled', false).html("<i class='fa fa-save'></i> Save Subjects");
                }
            },
            error: function() {
                smToast('Request failed.', 'error');
                $btn.prop('disabled', false).html("<i class='fa fa-save'></i> Save Subjects");
            }
        });
    });

    updateSummary();
});


/* ═══════════════════════════════════════════════════════════════
   clearSubjectsDisplay — fully wipes subject UI
═══════════════════════════════════════════════════════════════ */
function clearSubjectsDisplay() {
    var $holder = $('#subjects_holder');
    if ($holder.length) { $holder.empty(); $holder.off(); }
    $('.left-column').find('.subject-section').remove();
    $('.left-column').find('.assessment-info').remove();
    $('#selected_subjects').empty();
    $('.subject-btn, .subject-card, .subject-section, .stream-card')
        .removeClass('active category-invalid group-warning')
        .attr('aria-pressed', 'false');
    $('.subject-details, .subject-expanded').remove();
    $('.subject-btn').filter(function() { return !!$(this).attr('data-dropdown-lang'); }).remove();
    $('.language-dropdown, .subject-dropdown').each(function() {
        try { $(this).val(''); } catch(e) {}
    });
    updateSummary();
}


/* ═══════════════════════════════════════════════════════════════
   renderSubjects — ALL original logic, zero changes
═══════════════════════════════════════════════════════════════ */
function renderSubjects(subjectsData) {
    const leftColumn = $('.left-column');
    if (!leftColumn || !leftColumn.length) return;

    leftColumn.find('.subject-section').remove();
    $('#selected_subjects').empty();
    $('.subject-btn').removeClass('active').attr('aria-pressed', 'false');
    $('.language-dropdown, .subject-dropdown').val('');
    $('.subject-btn').filter(function() { return !!$(this).attr('data-dropdown-lang'); }).remove();
    $('#subjects_holder').off();

    if ($('#renderSubjectsValidationStyles').length === 0) {
        $('head').append(`<style id="renderSubjectsValidationStyles">
            .category-invalid { border: 2px solid #dc3545 !important; border-radius: 6px; padding: 8px; }
            .group-warning { outline: 2px solid #ffc107; }
        
/* ── Assessment modal body styles ── */
.sm-rules-group {
    margin-bottom: 10px;
    font-size: 13px;
    line-height: 1.6;
}
.sm-rules-group ul {
    margin: 4px 0 0 16px;
    padding: 0;
}
.sm-rules-group li { margin-bottom: 2px; }
.sm-assess-note {
    margin-top: 12px;
    padding: 10px 14px;
    background: #fff7ed;
    border-left: 3px solid var(--sm-amber);
    border-radius: 0 6px 6px 0;
    font-size: 12.5px;
    color: var(--sm-text);
}
.sm-assess-note p { margin: 4px 0 0; }
</style>`);
    }

    window.languageRules = window.languageRules || {};
    window.languageRules.min = parseInt(window.languageRules.min || 1, 10) || 1;
    window.languageRules.max = parseInt(window.languageRules.max || 2, 10) || 2;

    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");
    }

    function removeSelectedSubjectByName(name, group, dropdownLang = null) {
        if (!name || !group) return;
        const $selected = $('#selected_subjects');
        if (!$selected.length) return;
        const sel = $selected.find('.selected-subject').filter(function() {
            return String($(this).data('group')) === String(group) && String($(this).data('subject-name')) === String(name);
        }).first();
        if (!sel.length) return;
        const isDropdown     = !!sel.data('is-dropdown-created');
        const dropdownLangNm = sel.data('dropdown-lang');
        const associatedBtn  = sel.data('btn-element');
        sel.remove();
        if (isDropdown && dropdownLangNm) {
            let removed = $('.subject-btn').filter(function() {
                return String($(this).data('dropdown-lang')) === String(dropdownLangNm) &&
                       String($(this).data('group'))         === String(group) &&
                       String($(this).data('subject-name'))  === String(name);
            });
            if (removed.length) { removed.remove(); }
            else {
                $('.subject-btn').filter(function() {
                    return String($(this).data('dropdown-lang')) === String(dropdownLangNm);
                }).remove();
            }
        } else {
            if (associatedBtn) {
                const $orig = associatedBtn.jquery ? associatedBtn : $(associatedBtn);
                if ($orig && $orig.length) { $orig.removeClass('active').attr('aria-pressed','false'); }
                else {
                    $('.subject-btn').filter(function() {
                        return String($(this).data('group')) === String(group) && String($(this).data('subject-name')) === String(name);
                    }).removeClass('active').attr('aria-pressed','false');
                }
            } else {
                $('.subject-btn').filter(function() {
                    return String($(this).data('group')) === String(group) && String($(this).data('subject-name')) === String(name);
                }).removeClass('active').attr('aria-pressed','false');
            }
        }
        try {
            const section = $(`.subject-section[data-group="${group}"]`).first();
            if (typeof validateCategorySelection === 'function') validateCategorySelection(group, section, null);
        } catch(err) {}
        if (String(group) === 'Languages' && typeof window.updateLanguageGroupWarning === 'function') window.updateLanguageGroupWarning();
        updateSummary();
    }

    function isSubjectAlreadySelected(name, group) {
        const inList = $('#selected_subjects .selected-subject').filter(function() {
            return String($(this).data('group')) === String(group) && String($(this).data('subject-name')) === String(name);
        }).length > 0;
        const activeBtn = $('.subject-btn').filter(function() {
            return String($(this).data('group')) === String(group) && String($(this).data('subject-name')) === String(name) && $(this).hasClass('active');
        }).length > 0;
        return inList || activeBtn;
    }

    function normalizeToArray(val) {
        if (val === undefined || val === null) return [];
        if (Array.isArray(val)) return val.map(v => v === null || v === undefined ? '' : String(v));
        if (typeof val === 'object') return Object.values(val).map(v => v === null || v === undefined ? '' : String(v));
        return [String(val)];
    }

    function isMeaningfulText(s) {
        if (s === undefined || s === null) return false;
        const str = String(s).trim();
        if (str === '') return false;
        if (/^[+-]?\d+(\.\d+)?$/.test(str)) return false;
        return true;
    }

    function buildInfoText(isCompulsory, rulesObj) {
        const minVal = (rulesObj && (rulesObj.min_select !== undefined ? rulesObj.min_select : (rulesObj.min !== undefined ? rulesObj.min : null)));
        const maxVal = (rulesObj && (rulesObj.max_select !== undefined ? rulesObj.max_select : (rulesObj.max !== undefined ? rulesObj.max : null)));
        if (minVal !== null || maxVal !== null) {
            if (minVal !== null && maxVal !== null) return `Select minimum ${minVal} and maximum ${maxVal} subjects.`;
            else if (minVal !== null) return `Select minimum ${minVal} subjects.`;
            else return `Select maximum ${maxVal} subjects.`;
        }
        if (isCompulsory) return 'Compulsory as per syllabus';
        return 'Optional — select as per school availability';
    }

    function extractMinMax(rulesObj) {
        if (!rulesObj || typeof rulesObj !== 'object') return { min: null, max: null };
        const min = (rulesObj.min_select !== undefined ? Number(rulesObj.min_select) : (rulesObj.min !== undefined ? Number(rulesObj.min) : null));
        const max = (rulesObj.max_select !== undefined ? Number(rulesObj.max_select) : (rulesObj.max !== undefined ? Number(rulesObj.max) : null));
        return { min: Number.isFinite(min) ? min : null, max: Number.isFinite(max) ? max : null };
    }

    function validateCategorySelection(groupKey, sectionElement, rulesObj) {
        try {
            if (!sectionElement || !sectionElement.length) return;
            let mm = extractMinMax(rulesObj);
            if (mm.min === null && mm.max === null) {
                const rs = sectionElement.data('rules') || null;
                mm = extractMinMax(rs);
            }
            if (mm.min === null && mm.max === null) { sectionElement.removeClass('category-invalid'); return; }
            const namesSet = new Set();
            $('.subject-btn').filter(function() {
                return String($(this).data('group')) === String(groupKey) && $(this).hasClass('active');
            }).each(function() {
                const nm = String($(this).data('subject-name') || $(this).text()).trim();
                if (nm) namesSet.add(nm);
            });
            $('#selected_subjects .selected-subject').filter(function() {
                return String($(this).data('group')) === String(groupKey);
            }).each(function() {
                const nm = String($(this).data('subject-name') || $(this).text()).trim();
                if (nm) namesSet.add(nm);
            });
            const count = namesSet.size;
            let invalid = false;
            if (mm.min !== null && count < mm.min) invalid = true;
            if (mm.max !== null && count > mm.max) invalid = true;
            if (invalid) sectionElement.addClass('category-invalid');
            else sectionElement.removeClass('category-invalid');
        } catch(err) {}
    }

    if (!subjectsData || typeof subjectsData !== 'object') return;

    /* ── selectSubject (original logic) ── */
    function selectSubject(subject, btn, group, dropdownLangName = null) {
        const selectedContainer = $('#selected_subjects');
        if (!selectedContainer.length) return;
        const $btn = btn ? (btn.jquery ? btn : $(btn)) : null;
        const already = selectedContainer.find('.selected-subject').filter(function() {
            return $(this).data('group') === group && $(this).data('subject-name') === subject;
        }).length;
        if (already) return;

        const category = $btn?.data('category') || group;
        const stream   = $btn?.data('stream') || 'common';

        const div = $('<div>').addClass('selected-subject')
            .data('group', group).data('category', category).data('stream', stream).data('subject-name', subject)
            .attr('data-group', group).attr('data-category', category).attr('data-stream', stream).attr('data-subject-name', subject);

        const labelSpan = $('<span>').addClass('ss-name').text(subject);
        const catBadge  = $('<span>').addClass('ss-cat').text(group);
        const removeBtn = $('<button type="button">').addClass('remove-btn').html('&times;');

        removeBtn.on('click', function() {
            div.remove();
            if ($btn) $btn.removeClass('active').attr('aria-pressed','false');
            updateSummary();
        });

        div.append(labelSpan).append(catBadge).append(removeBtn);
        selectedContainer.append(div);
        if ($btn) $btn.addClass('active').attr('aria-pressed','true');
        updateSummary();
    }

    /* ── Render each group ── */
    $.each(subjectsData, function(group, data) {

        /* Assessment */
        if (group === 'Assessment') {
            function makeAssessmentClickHandler(dataObj, subjectsDataObj) {
                return function(e) {
                    if (e && typeof e.preventDefault === 'function') { e.preventDefault(); e.stopPropagation(); }
                    const schemeItems = [];
                    if (dataObj.grading_scale) schemeItems.push(`<li><strong>Grading Scale:</strong> ${escapeHtml(String(dataObj.grading_scale))}</li>`);
                    if (dataObj.internal_assessment_marks !== undefined) schemeItems.push(`<li><strong>Internal Assessment Marks:</strong> ${escapeHtml(String(dataObj.internal_assessment_marks))}</li>`);
                    if (dataObj.theory_marks !== undefined) schemeItems.push(`<li><strong>Theory Marks:</strong> ${escapeHtml(String(dataObj.theory_marks))}</li>`);
                    if (Array.isArray(dataObj.observation_areas) && dataObj.observation_areas.length)
                        schemeItems.push(`<li><strong>Observation Areas:</strong> ${dataObj.observation_areas.map(x => escapeHtml(String(x))).join(', ')}</li>`);
                    for (const k in dataObj) {
                        if (['grading_scale','internal_assessment_marks','theory_marks','observation_areas'].includes(k)) continue;
                        if (k.toLowerCase() === 'note') continue;
                        const v = dataObj[k];
                        if (typeof v === 'string' || typeof v === 'number' || typeof v === 'boolean') schemeItems.push(`<li><strong>${escapeHtml(k)}:</strong> ${escapeHtml(String(v))}</li>`);
                        else if (Array.isArray(v)) schemeItems.push(`<li><strong>${escapeHtml(k)}:</strong> ${v.map(x=>escapeHtml(String(x))).join(', ')}</li>`);
                        else if (typeof v === 'object' && v !== null) schemeItems.push(`<li><strong>${escapeHtml(k)}:</strong> ${escapeHtml(JSON.stringify(v))}</li>`);
                    }
                    const schemeHtml = schemeItems.length ? `<ul class="mb-2">${schemeItems.join('')}</ul>` : `<p><em>No assessment scheme information available.</em></p>`;

                    // ── Rules processing (restored from original) ──
                    const normKey = k => String(k).replace(/[^a-z0-9]+/gi,'_').replace(/^_+|_+$/g,'').toLowerCase();
                    const titleKeyFn = t => String(t).replace(/[^a-z0-9]+/gi,' ').replace(/\s+/g,' ').trim().toLowerCase();
                    const addUnique = (arr, items) => { for (const it of items) if (!arr.includes(it)) arr.push(it); };
                    const toStrings = raw => {
                        if (raw == null) return [];
                        if (Array.isArray(raw)) return raw.map(x=>String(x)).map(s=>s.trim()).filter(Boolean);
                        if (typeof raw === 'object') {
                            if (Object.prototype.hasOwnProperty.call(raw,'rules')) return toStrings(raw.rules);
                            const keys = Object.keys(raw);
                            const numeric = keys.filter(k=>/^\d+$/.test(k)).sort((a,b)=>Number(a)-Number(b));
                            if (numeric.length) return numeric.map(k=>raw[k]).map(x=>String(x)).map(s=>s.trim()).filter(Boolean);
                            return Object.values(raw).map(x=>String(x)).map(s=>s.trim()).filter(Boolean);
                        }
                        return [String(raw).trim()].filter(Boolean);
                    };
                    const isSelectMeta = key => { const kk=normKey(key); return kk.endsWith('_max_select')||kk.endsWith('_min_select')||kk.includes('max_select')||kk.includes('min_select'); };
                    const categoryMap = new Map();
                    const addCat = (title, vals) => {
                        if (!title||!vals||!vals.length) return;
                        const nk = titleKeyFn(title);
                        if (!categoryMap.has(nk)) { const u=[]; for(const v of vals) if(!u.includes(v)) u.push(v); categoryMap.set(nk,{displayTitle:title,values:u}); }
                        else { addUnique(categoryMap.get(nk).values, vals); }
                    };
                    let rulesSource = null;
                    if (subjectsDataObj && subjectsDataObj.Core && subjectsDataObj.Core.rules !== undefined) rulesSource = subjectsDataObj.Core.rules;
                    else if (subjectsDataObj && subjectsDataObj.rules !== undefined) rulesSource = subjectsDataObj.rules;
                    const general = [];
                    if (subjectsDataObj && subjectsDataObj.Core && typeof subjectsDataObj.Core === 'object') {
                        Object.entries(subjectsDataObj.Core).forEach(([kk,vv]) => {
                            if (vv && typeof vv === 'object' && vv.rules !== undefined) {
                                const t = (kk==='Core'||kk.toLowerCase()==='rules') ? null : kk;
                                const vs = toStrings(vv.rules);
                                if (!vs.length) return;
                                t ? addCat(t,vs) : addUnique(general,vs);
                            }
                        });
                    }
                    if (rulesSource && typeof rulesSource === 'object' && Object.prototype.hasOwnProperty.call(rulesSource,'rules')) {
                        const nested = rulesSource.rules;
                        if (Array.isArray(nested)) addUnique(general,toStrings(nested));
                        else if (nested && typeof nested === 'object') rulesSource = Object.assign({},rulesSource,nested);
                        else addUnique(general,toStrings(nested));
                    }
                    if (Array.isArray(rulesSource)) addUnique(general,toStrings(rulesSource));
                    else if (rulesSource && typeof rulesSource === 'object') {
                        Object.entries(rulesSource).forEach(([rawKey,rawVal]) => {
                            if (isSelectMeta(rawKey)) return;
                            const key=String(rawKey); const k=normKey(key); const vs=toStrings(rawVal);
                            if (!vs.length) return;
                            if (/^\d+$/.test(key)||k==='rules'||k.includes('core')) { addUnique(general,vs); return; }
                            if (/_?rules?$/.test(k)||k.includes('rule')) {
                                let t = k.replace(/_?rules?$/i,'').replace(/_/g,' ').trim();
                                if (!t) t = key;
                                t = t.split(/\s+/).map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
                                addCat(t,vs);
                            }
                        });
                    }
                    if (subjectsDataObj && typeof subjectsDataObj === 'object') {
                        Object.entries(subjectsDataObj).forEach(([topKey,topVal]) => {
                            if (topKey==='Core'||topKey==='rules') return;
                            if (topVal && typeof topVal === 'object' && topVal.rules !== undefined) addCat(topKey,toStrings(topVal.rules));
                        });
                    }
                    const mergedCategories = [];
                    for (const [,{displayTitle,values}] of categoryMap.entries()) mergedCategories.push({title:displayTitle,values});
                    const dedupGeneral = [];
                    for (const r of general) if (!dedupGeneral.includes(r)) dedupGeneral.push(r);
                    const rParts = [];
                    if (dedupGeneral.length) rParts.push(`<div class="sm-rules-group"><strong>General rules:</strong><ul>${dedupGeneral.map(r=>`<li>${escapeHtml(r)}</li>`).join('')}</ul></div>`);
                    mergedCategories.forEach(cat => { if (!cat.values.length) return; rParts.push(`<div class="sm-rules-group"><strong>For ${escapeHtml(cat.title)}:</strong><ul>${cat.values.map(v=>`<li>${escapeHtml(v)}</li>`).join('')}</ul></div>`); });
                    if (!rParts.length) rParts.push(`<p><em>No rules available for this class.</em></p>`);
                    const rulesHtml = rParts.join('');

                    // ── Note ──
                    let note = null;
                    if (dataObj && dataObj.note) note = dataObj.note;
                    else if (subjectsDataObj && subjectsDataObj.Core && subjectsDataObj.Core.note) note = subjectsDataObj.Core.note;
                    else if (subjectsDataObj && subjectsDataObj.note) note = subjectsDataObj.note;
                    const noteHtml = note ? `<div class="sm-assess-note"><strong>Note:</strong><p>${escapeHtml(String(note))}</p></div>` : '';
                    const html = `
                        <div>
                            <div class="mb-2"><strong>Assessment Scheme:</strong>${schemeHtml}</div>
                            <div class="mb-2"><strong>Rules:</strong>${rulesHtml}</div>
                            ${noteHtml}
                        </div>
                    `;
                    showAssessmentModal(html);
                };
            }

            const existing    = leftColumn.find('.assessment-info').first();
            const clickHandler = makeAssessmentClickHandler(data, subjectsData);
            if (existing.length) {
                const btn = existing.find('button.sm-assess-btn');
                if (btn.length) btn.off('click').on('click', function(e){ e.preventDefault(); e.stopPropagation(); clickHandler(e); });
                else existing.append($('<button type="button">').addClass('sm-assess-btn sm-btn sm-btn-info sm-btn-sm').html('<i class="fa fa-info-circle"></i> View Assessment Info').on('click', function(e){ e.preventDefault(); e.stopPropagation(); clickHandler(e); }));
            } else {
                leftColumn.prepend(
                    $('<div>').addClass('assessment-info sm-assess-banner')
                    .append('<span><i class="fa fa-bar-chart"></i> Assessment Scheme &amp; Rules</span>')
                    .append($('<button type="button">').addClass('sm-assess-btn sm-btn sm-btn-info sm-btn-sm').html('<i class="fa fa-eye"></i> View Details').on('click', function(e){ e.preventDefault(); e.stopPropagation(); clickHandler(e); }))
                );
            }
            return;
        }

        /* Streams */
        if (group === 'Streams' && data && typeof data === 'object') {
            const sectionDiv = $('<div>').addClass('subject-section sm-mb').attr('data-group', group).data('rules', data);
            sectionDiv.append($('<div>').addClass('subject-row').append($('<div>').addClass('subject-label').html('<strong>Streams</strong>')));
            const streamsContainer = $('<div>').addClass('streams-container mt-2');

            Object.entries(data).forEach(([streamName, streamDef]) => {
                if (!streamDef || typeof streamDef !== 'object') return;
                const streamGroupKey = `Streams:${streamName}`;
                const streamCard = $('<div>').addClass('stream-card sm-stream-card').attr('data-stream', streamName).data('rules', streamDef);
                streamCard.append($('<div>').addClass('stream-heading').html(`<i class="fa fa-code-fork"></i> <strong>${escapeHtml(streamName)}</strong>`));

                const coreListDiv  = $('<div>').addClass('stream-core sm-mb');
                coreListDiv.append($('<div>').addClass('sm-sublabel').html('<i class="fa fa-lock"></i> Core'));
                const coreBtnsDiv  = $('<div>').addClass('subject-buttons');
                const coreSubjects = Array.isArray(streamDef.core_subjects) ? streamDef.core_subjects : [];

                coreSubjects.forEach(sub => {
                    const btn = $('<button>').attr('type','button').addClass('subject-btn').text(sub)
                        .data('group', streamGroupKey).attr('data-group', streamGroupKey)
                        .data('subject-name', sub).attr('data-subject-name', sub)
                        .data('stream', streamName).attr('data-stream', streamName)
                        .data('category','Core').attr('data-category','Core')
                        .attr('aria-pressed','false')
                        .on('click', function() {
                            const b = $(this); const nm = String(b.data('subject-name'));
                            if (b.hasClass('active')) { removeSelectedSubjectByName(nm, streamGroupKey); b.removeClass('active').attr('aria-pressed','false'); }
                            else { if (!isSubjectAlreadySelected(nm, streamGroupKey)) { selectSubject(nm, b, streamGroupKey); b.addClass('active').attr('aria-pressed','true'); } }
                            validateCategorySelection(streamGroupKey, streamCard, streamDef);
                            validateCategorySelection('Streams', sectionDiv, data);
                        });
                    if (streamDef.compulsory === true) {
                        btn.addClass('active').attr('aria-pressed','true');
                        if (!isSubjectAlreadySelected(sub, streamGroupKey)) selectSubject(sub, btn, streamGroupKey);
                    }
                    coreBtnsDiv.append(btn);
                });
                coreListDiv.append(coreBtnsDiv);
                streamCard.append(coreListDiv);

                const optionalListDiv = $('<div>').addClass('stream-optional');
                optionalListDiv.append($('<div>').addClass('sm-sublabel').html('<i class="fa fa-unlock-alt"></i> Optional'));
                const optionalSubjects = Array.isArray(streamDef.optional_subjects) ? streamDef.optional_subjects : [];

                if (optionalSubjects.length > 0) {
                    const optDropdown = $('<select>').addClass('sm-select subject-dropdown stream-optional-dropdown sm-mb')
                        .append($('<option>').val('').text('Select Optional Subject'));
                    optionalSubjects.forEach(sub => optDropdown.append($('<option>').val(String(sub)).text(sub)));
                    optDropdown.on('change', function() {
                        const selectedSub = $(this).val();
                        if (!selectedSub) return;
                        if (isSubjectAlreadySelected(selectedSub, streamGroupKey)) { $(this).val(''); return; }
                        const btn = $('<button>').attr('type','button').addClass('subject-btn active').text(selectedSub)
                            .data('group', streamGroupKey).attr('data-group', streamGroupKey)
                            .data('subject-name', selectedSub).attr('data-subject-name', selectedSub)
                            .data('stream', streamName).attr('data-stream', streamName)
                            .data('category','Optional').attr('data-category','Optional')
                            .data('is-dropdown-created', true).attr('data-dropdown-lang', selectedSub)
                            .on('click', function() {
                                const b = $(this); const nm = String(b.data('subject-name'));
                                if (b.hasClass('active')) { removeSelectedSubjectByName(nm, streamGroupKey, nm); b.removeClass('active'); }
                                else { selectSubject(nm, b, streamGroupKey, nm); b.addClass('active'); }
                                validateCategorySelection(streamGroupKey, streamCard, streamDef);
                                validateCategorySelection('Streams', sectionDiv, data);
                            });
                        $(this).before(btn);
                        selectSubject(selectedSub, btn, streamGroupKey, selectedSub);
                        validateCategorySelection(streamGroupKey, streamCard, streamDef);
                        validateCategorySelection('Streams', sectionDiv, data);
                        $(this).val('');
                    });
                    optionalListDiv.append(optDropdown);
                }

                const explicit = { min: streamDef.min_select ?? streamDef.min ?? null, max: streamDef.max_select ?? streamDef.max ?? null };
                let streamInfoStr;
                if (explicit.min !== null || explicit.max !== null) {
                    if (explicit.min !== null && explicit.max !== null) streamInfoStr = `Select minimum ${explicit.min} and maximum ${explicit.max} subjects.`;
                    else if (explicit.min !== null) streamInfoStr = `Select minimum ${explicit.min} subjects.`;
                    else streamInfoStr = `Select maximum ${explicit.max} subjects.`;
                } else {
                    streamInfoStr = buildInfoText(!!streamDef.compulsory, streamDef.rules || {});
                }
                optionalListDiv.append($('<small>').addClass('sm-info-text').text(streamInfoStr));
                streamCard.append(optionalListDiv);
                streamsContainer.append(streamCard);
                validateCategorySelection(streamGroupKey, streamCard, streamDef);
            });

            sectionDiv.append(streamsContainer);
            leftColumn.append(sectionDiv);
            validateCategorySelection('Streams', sectionDiv, data);
            return;
        }

        /* Standard groups */
        const sectionDiv  = $('<div>').addClass('subject-section sm-mb').attr('data-group', group);
        const rowDiv      = $('<div>').addClass('subject-row');
        const labelDiv    = $('<div>').addClass('subject-label').html(`<strong>${escapeHtml(group)}</strong>`);
        const buttonsDiv  = $('<div>').addClass('subject-buttons');

        let subjects = [], rules = {}, isCompulsory = false;
        if (typeof data === 'object' && data.options) {
            if (Array.isArray(data.options)) subjects = data.options.slice();
            else if (typeof data.options === 'object') subjects = Object.values(data.options).slice();
            rules = data.rules || {};
            isCompulsory = data.compulsory === true;
        } else if (Array.isArray(data)) {
            subjects = data.slice();
        }

        if (group !== 'Additional' && (!subjects || subjects.length === 0)) return;
        sectionDiv.data('rules', rules || {});

        const infoTextStr = buildInfoText(isCompulsory, rules);
        const infoText = $('<small>').addClass('sm-info-text').text(infoTextStr);

        /* Languages */
        if (group === 'Languages') {
            window.languageRules.min = parseInt(rules.min_select || rules.min || window.languageRules.min || 1, 10) || 1;
            window.languageRules.max = parseInt(rules.max_select || rules.max || window.languageRules.max || 2, 10) || 2;

            const englishBtn = $('<button>').attr('type','button').addClass('subject-btn').text('English')
                .data('group','Languages').attr('data-group','Languages')
                .data('subject-name','English').attr('data-subject-name','English')
                .on('click', function() {
                    const btn = $(this); const name = 'English';
                    if (btn.hasClass('active')) { removeSelectedSubjectByName(name,'Languages'); btn.removeClass('active').attr('aria-pressed','false'); }
                    else { if (!isSubjectAlreadySelected(name,'Languages')) { selectSubject && selectSubject(name, btn,'Languages'); btn.addClass('active').attr('aria-pressed','true'); } }
                    if (typeof updateLanguageGroupWarning === 'function') updateLanguageGroupWarning();
                    validateCategorySelection('Languages', sectionDiv, rules);
                });

            const dropdown = $('<select>').addClass('sm-select language-dropdown')
                .append($('<option>').val('').text('Select Other Language'));
            subjects.forEach(sub => { if (sub !== 'English') dropdown.append($('<option>').val(String(sub)).text(sub)); });

            dropdown.on('change', function() {
                const selectedLang = $(this).val();
                if (!selectedLang) return;
                const totalSelected = $('#selected_subjects .selected-subject').filter((i, el) => String($(el).data('group')) === 'Languages').length;
                if (totalSelected >= window.languageRules.max) { smToast(`Maximum ${window.languageRules.max} languages allowed.`,'warning'); $(this).val(''); return; }
                if (isSubjectAlreadySelected(selectedLang,'Languages')) { $(this).val(''); return; }
                const langBtn = $('<button>').attr('type','button').addClass('subject-btn active').text(selectedLang)
                    .data('group','Languages').attr('data-group','Languages')
                    .data('subject-name', selectedLang).attr('data-subject-name', selectedLang)
                    .attr('data-dropdown-lang', selectedLang)
                    .on('click', function() {
                        const b=$(this); const nm=String($(this).data('subject-name')||$(this).text());
                        if (b.hasClass('active')) { removeSelectedSubjectByName(nm,'Languages',nm); b.removeClass('active').attr('aria-pressed','false'); }
                        else { selectSubject && selectSubject(nm,b,'Languages',nm); b.addClass('active').attr('aria-pressed','true'); }
                        validateCategorySelection('Languages', sectionDiv, rules);
                    });
                $(this).before(langBtn);
                selectSubject && selectSubject(selectedLang, langBtn,'Languages', selectedLang);
                $(this).val('');
                if (typeof updateLanguageGroupWarning === 'function') updateLanguageGroupWarning();
                validateCategorySelection('Languages', sectionDiv, rules);
            });
            buttonsDiv.append(englishBtn).append(dropdown).append(infoText);
            validateCategorySelection('Languages', sectionDiv, rules);
        }

        /* Compulsory */
        else if (isCompulsory) {
            subjects.forEach(subject => {
                const btn = $('<button>').attr('type','button').addClass('subject-btn active').text(subject)
                    .data('group', group).attr('data-group', group)
                    .data('subject-name', subject).attr('data-subject-name', subject)
                    .on('click', function() {
                        const b=$(this); const nm=String($(this).data('subject-name')||$(this).text());
                        if (b.hasClass('active')) { removeSelectedSubjectByName(nm,group); b.removeClass('active').attr('aria-pressed','false'); }
                        else { if (!isSubjectAlreadySelected(nm,group)) { selectSubject && selectSubject(nm,btn,group); btn.addClass('active').attr('aria-pressed','true'); } }
                        validateCategorySelection(group, sectionDiv, rules);
                    });
                buttonsDiv.append(btn);
                if (!isSubjectAlreadySelected(subject,group)) selectSubject && selectSubject(subject, btn, group);
            });
            buttonsDiv.append(infoText);
            validateCategorySelection(group, sectionDiv, rules);
        }

        /* Optional */
        else {
            buttonsDiv.find('.subject-dropdown').remove();

            if (group === 'Additional') {
                let dropdown = null;
                const cleanedOptions = Array.isArray(subjects) ? subjects.map(s=>(s===null||s===undefined)?'':String(s).trim()).filter(s=>s!==''&&s.toLowerCase()!=='false') : [];
                if (cleanedOptions.length > 0) {
                    dropdown = $('<select>').addClass('sm-select subject-dropdown').append($('<option>').val('').text('Select Subject'));
                    cleanedOptions.forEach(sub => dropdown.append($('<option>').val(String(sub)).text(sub)));
                }
                const inputGroup   = $('<div>').addClass('sm-add-input-group');
                const customInput  = $('<input>').attr('type','text').addClass('sm-input sm-additional-input').attr('placeholder','Type custom subject & press Enter');
                const addBtn       = $('<button>').attr('type','button').addClass('sm-btn sm-btn-teal sm-btn-sm').html('<i class="fa fa-plus"></i> Add');

                function createAndSelectSubject(name) {
                    name = String(name||'').trim();
                    if (!isMeaningfulText(name)) { smToast('Enter a valid subject name.','warning'); return; }
                    if (isSubjectAlreadySelected(name, group)) return;
                    const btn = $('<button>').attr('type','button').addClass('subject-btn active').text(name)
                        .data('group',group).attr('data-group',group)
                        .data('subject-name',name).attr('data-subject-name',name)
                        .data('is-dropdown-created',true).attr('data-dropdown-lang',name)
                        .on('click', function() {
                            const b=$(this); const nm=String($(this).data('subject-name')||$(this).text());
                            if (b.hasClass('active')) { removeSelectedSubjectByName(nm,group,nm); b.removeClass('active').attr('aria-pressed','false'); }
                            else { selectSubject && selectSubject(nm,b,group,nm); b.addClass('active').attr('aria-pressed','true'); }
                            validateCategorySelection(group, sectionDiv, rules);
                        });
                    if (dropdown && dropdown.parent().length) dropdown.before(btn); else buttonsDiv.append(btn);
                    selectSubject && selectSubject(name, btn, group, name);
                    if (dropdown) { if (dropdown.find(`option[value="${name}"]`).length === 0) dropdown.append($('<option>').val(name).text(name)); }
                    if (!subjects.includes(name)) subjects.push(name);
                    customInput.val('');
                    validateCategorySelection(group, sectionDiv, rules);
                }

                addBtn.on('click', function() { createAndSelectSubject(customInput.val()); });
                customInput.on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); createAndSelectSubject($(this).val()); } });

                if (dropdown) {
                    dropdown.on('change', function() {
                        const sel = $(this).val();
                        if (!sel) return;
                        if (isSubjectAlreadySelected(sel,group)) { $(this).val(''); return; }
                        const btn = $('<button>').attr('type','button').addClass('subject-btn active').text(sel)
                            .data('group',group).attr('data-group',group)
                            .data('subject-name',sel).attr('data-subject-name',sel)
                            .data('is-dropdown-created',true).attr('data-dropdown-lang',sel)
                            .on('click', function() {
                                const b=$(this); const nm=String($(this).data('subject-name')||$(this).text());
                                if (b.hasClass('active')) { removeSelectedSubjectByName(nm,group,nm); b.removeClass('active').attr('aria-pressed','false'); }
                                else { selectSubject && selectSubject(nm,b,group,nm); b.addClass('active').attr('aria-pressed','true'); }
                                validateCategorySelection(group, sectionDiv, rules);
                            });
                        $(this).before(btn);
                        selectSubject && selectSubject(sel, btn, group, sel);
                        validateCategorySelection(group, sectionDiv, rules);
                        $(this).val('');
                    });
                    buttonsDiv.append(dropdown);
                }
                inputGroup.append(customInput).append(addBtn);
                buttonsDiv.append(inputGroup).append(infoText);
                validateCategorySelection(group, sectionDiv, rules);
            } else {
                const dropdown = $('<select>').addClass('sm-select subject-dropdown').append($('<option>').val('').text('Select Subject'));
                subjects.forEach(sub => dropdown.append($('<option>').val(String(sub)).text(sub)));
                dropdown.on('change', function() {
                    const sel = $(this).val();
                    if (!sel) return;
                    if (isSubjectAlreadySelected(sel,group)) { $(this).val(''); return; }
                    const btn = $('<button>').attr('type','button').addClass('subject-btn active').text(sel)
                        .data('group',group).attr('data-group',group)
                        .data('subject-name',sel).attr('data-subject-name',sel)
                        .data('is-dropdown-created',true).attr('data-dropdown-lang',sel)
                        .on('click', function() {
                            const b=$(this); const nm=String($(this).data('subject-name')||$(this).text());
                            if (b.hasClass('active')) { removeSelectedSubjectByName(nm,group,nm); b.removeClass('active').attr('aria-pressed','false'); }
                            else { selectSubject && selectSubject(nm,b,group,nm); b.addClass('active').attr('aria-pressed','true'); }
                            validateCategorySelection(group, sectionDiv, rules);
                        });
                    $(this).before(btn);
                    selectSubject && selectSubject(sel, btn, group, sel);
                    validateCategorySelection(group, sectionDiv, rules);
                    $(this).val('');
                });
                buttonsDiv.append(dropdown).append(infoText);
                validateCategorySelection(group, sectionDiv, rules);
            }
        }

        rowDiv.append(labelDiv).append(buttonsDiv);
        sectionDiv.append(rowDiv);
        leftColumn.append(sectionDiv);
    });
}

/* ── updateLanguageGroupWarning ── */
window.updateLanguageGroupWarning = function() {
    const languagesSelected = $('#selected_subjects .selected-subject').filter((i, el) => String($(el).data('group')) === 'Languages').length;
    const languagesGroupDiv = $('.subject-section[data-group="Languages"]');
    if (languagesGroupDiv.length) {
        window.languageRules = window.languageRules || {};
        const min = Number.isFinite(Number(window.languageRules.min)) ? Number(window.languageRules.min) : 1;
        if (languagesSelected > 0 && languagesSelected < min) languagesGroupDiv.addClass('group-warning');
        else languagesGroupDiv.removeClass('group-warning');
    }
};
</script>


<!-- ══ STYLES ══ -->
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

:root {
    --sm-navy:    #0b1f3a;
    --sm-teal:    #0e7490;
    --sm-sky:     #e0f2fe;
    --sm-green:   #16a34a;
    --sm-red:     #dc2626;
    --sm-amber:   #d97706;
    --sm-blue:    #2563eb;
    --sm-purple:  #7c3aed;
    --sm-text:    #1e293b;
    --sm-muted:   #64748b;
    --sm-border:  #e2e8f0;
    --sm-white:   #ffffff;
    --sm-bg:      #f1f5f9;
    --sm-shadow:  0 1px 14px rgba(11,31,58,.08);
    --sm-radius:  12px;
}
* { box-sizing: border-box; }

/* ── Shell ── */
.sm-wrap {
    font-family: 'DM Sans', sans-serif;
    background: var(--sm-bg);
    color: var(--sm-text);
    padding: 24px 20px 60px;
    min-height: 100vh;
}

/* ── Top bar ── */
.sm-topbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 22px;
}
.sm-page-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--sm-navy);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 5px;
}
.sm-page-title i { color: var(--sm-teal); }
.sm-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 12.5px;
    color: var(--sm-muted);
}
.sm-breadcrumb a { color: var(--sm-teal); text-decoration: none; font-weight: 500; }
.sm-breadcrumb li + li::before { content: '/'; margin: 0 7px; color: #cbd5e1; }

.sm-school-badge {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}
.sm-badge-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: var(--sm-muted);
}
.sm-badge-val {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--sm-navy);
    line-height: 1;
}

/* ── Layout ── */
.sm-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 18px;
    align-items: start;
}
@media (max-width: 960px) {
    .sm-layout { grid-template-columns: 1fr; }
    .sm-right  { order: -1; }
}

/* ── Card ── */
.sm-card {
    background: var(--sm-white);
    border-radius: var(--sm-radius);
    box-shadow: var(--sm-shadow);
    border: 1px solid var(--sm-border);
    margin-bottom: 16px;
    overflow: hidden;
}
.sm-card-head {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 13px 18px;
    border-bottom: 1px solid var(--sm-border);
    background: linear-gradient(90deg, var(--sm-sky) 0%, var(--sm-white) 100%);
}
.sm-step {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--sm-teal);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sm-card-head i { color: var(--sm-teal); flex-shrink: 0; }
.sm-card-head h3 {
    font-family: 'Playfair Display', serif;
    font-size: 14.5px;
    font-weight: 700;
    color: var(--sm-navy);
    margin: 0;
}
.sm-head-hint { font-size: 11.5px; color: var(--sm-muted); font-weight: 400; margin-left: 4px; }
.sm-card-body { padding: 18px; }

/* ── Fields + Selects ── */
.sm-field { display: flex; flex-direction: column; gap: 4px; }
.sm-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: var(--sm-muted);
}
.sm-req { color: var(--sm-red); }
.sm-select-wrap { position: relative; }
.sm-select {
    height: 38px;
    padding: 0 32px 0 10px;
    border: 1.5px solid var(--sm-border);
    border-radius: 8px;
    font-size: 13.5px;
    color: var(--sm-text);
    background: #fafcff;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    width: 100%;
    appearance: none;
    cursor: pointer;
    transition: border-color .13s, box-shadow .13s;
}
.sm-select:focus { border-color: var(--sm-teal); box-shadow: 0 0 0 3px rgba(14,116,144,.1); }
.sm-select-arr {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sm-muted);
    font-size: 10px;
    pointer-events: none;
}
.sm-input {
    height: 38px;
    padding: 0 10px;
    border: 1.5px solid var(--sm-border);
    border-radius: 8px;
    font-size: 13.5px;
    font-family: 'DM Sans', sans-serif;
    color: var(--sm-text);
    background: #fafcff;
    outline: none;
    transition: border-color .13s;
    flex: 1;
}
.sm-input:focus { border-color: var(--sm-teal); box-shadow: 0 0 0 3px rgba(14,116,144,.1); }

/* ── Grid ── */
.sm-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.sm-mb { margin-bottom: 14px; }
@media (max-width: 600px) { .sm-grid-2 { grid-template-columns: 1fr; } }

/* ── Pattern buttons ── */
.sm-pattern-toggle { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.sm-pattern-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border: 1.5px solid var(--sm-border);
    border-radius: 8px;
    background: var(--sm-white);
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--sm-text);
    cursor: pointer;
    transition: all .13s;
}
.sm-pattern-btn:hover { border-color: var(--sm-teal); color: var(--sm-teal); }
.sm-pattern-btn.active { background: var(--sm-teal); color: #fff; border-color: var(--sm-teal); }

/* ── Assessment banner ── */
.sm-assess-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: linear-gradient(90deg, #fff7ed 0%, #fef9f0 100%);
    border: 1px solid #fed7aa;
    border-radius: 8px;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--sm-amber);
}
.sm-assess-banner i { margin-right: 6px; }

/* ── Subject sections (inherited from original + refined) ── */
.subject-section {
    border: 1px solid var(--sm-border);
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 12px;
    background: var(--sm-white);
    transition: border-color .15s;
}
.subject-section:hover { border-color: #cbd5e1; }
.subject-row { display: flex; align-items: flex-start; gap: 16px; }
.subject-label {
    flex: 0 0 130px;
    padding-top: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--sm-navy);
}
.subject-buttons {
    flex: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.subject-btn {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border: 1.5px solid var(--sm-border);
    border-radius: 20px;
    padding: 6px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--sm-text);
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.subject-btn:hover {
    background: var(--sm-sky);
    border-color: var(--sm-teal);
    color: var(--sm-teal);
    transform: translateY(-1px);
}
.subject-btn.active {
    background: linear-gradient(135deg, var(--sm-teal) 0%, #0c6682 100%);
    color: #fff;
    border-color: var(--sm-teal);
    box-shadow: 0 3px 10px rgba(14,116,144,.25);
}
.subject-btn.active:hover { opacity: .88; }

.sm-info-text {
    display: block;
    width: 100%;
    margin-top: 6px;
    font-size: 11px;
    color: var(--sm-muted);
    font-style: italic;
}
.sm-sublabel {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: var(--sm-muted);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Streams ── */
.sm-stream-card {
    border: 1px solid var(--sm-border);
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 10px;
    background: #f8fafc;
}
.stream-heading {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--sm-navy);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.stream-heading i { color: var(--sm-teal); }
.streams-container { padding-left: 4px; }

/* ── Additional subject input ── */
.sm-add-input-group {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 8px;
    flex-wrap: wrap;
}
.sm-additional-input { min-width: 200px; }

/* ── Validation states ── */
.category-invalid {
    border: 2px solid var(--sm-red) !important;
    border-radius: 8px;
    background: #fef2f2 !important;
}
.group-warning {
    border: 2px solid var(--sm-amber) !important;
    border-radius: 8px;
    background: #fffbeb !important;
}

/* ── Loading / empty ── */
.sm-loading, .sm-empty-msg {
    text-align: center;
    color: var(--sm-muted);
    padding: 40px 20px;
    font-size: 14px;
}
.sm-loading i, .sm-empty-msg i { font-size: 22px; margin-right: 8px; color: var(--sm-teal); }

/* ── RIGHT: Summary Card ── */
.sm-right { position: sticky; top: 16px; max-height: calc(100vh - 32px); display: flex; flex-direction: column; }
.sm-summary-card {
    background: var(--sm-navy);
    border-radius: var(--sm-radius);
    box-shadow: 0 4px 24px rgba(11,31,58,.2);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 32px);
    overflow: hidden;
}
.sm-summary-head {
    padding: 14px 18px;
    color: rgba(255,255,255,.75);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(255,255,255,.1);
    display: flex;
    align-items: center;
    gap: 8px;
}
.sm-summary-body {
    flex: 1;
    overflow-y: auto;
    padding: 14px 18px;
}
.sm-summary-body::-webkit-scrollbar { width: 4px; }
.sm-summary-body::-webkit-scrollbar-track { background: rgba(255,255,255,.05); }
.sm-summary-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

.sm-summary-meta { margin-bottom: 10px; }
.sm-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255,255,255,.07);
    font-size: 12.5px;
    color: rgba(255,255,255,.6);
}
.sm-summary-row strong { color: rgba(255,255,255,.9); font-size: 13px; }
.sm-summary-divider { border-bottom: 1px solid rgba(255,255,255,.15); margin: 8px 0; }

/* Selected subjects list */
.sm-selected-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.selected-subject {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    background: rgba(14,116,144,.2);
    border: 1px solid rgba(14,116,144,.35);
    border-radius: 8px;
    transition: background .13s;
}
.selected-subject:hover { background: rgba(14,116,144,.3); }
.ss-name {
    flex: 1;
    font-size: 12.5px;
    font-weight: 600;
    color: rgba(255,255,255,.92);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ss-cat {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: rgba(255,255,255,.45);
    flex-shrink: 0;
}
.remove-btn {
    border: none;
    background: transparent;
    color: rgba(255,255,255,.5);
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    padding: 0 3px;
    line-height: 1;
    flex-shrink: 0;
    transition: color .13s, transform .1s;
}
.remove-btn:hover { color: #f87171; transform: scale(1.2); }

.sm-selected-empty {
    text-align: center;
    padding: 30px 16px;
    color: rgba(255,255,255,.35);
    font-size: 12.5px;
}
.sm-selected-empty i { display: block; font-size: 24px; margin-bottom: 8px; opacity: .4; }
.sm-selected-empty p { margin: 0; line-height: 1.5; }

/* ── Summary actions ── */
.sm-summary-actions {
    padding: 14px 18px;
    border-top: 1px solid rgba(255,255,255,.1);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* ── Buttons ── */
.sm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 8px;
    border: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: opacity .13s, transform .1s;
    width: 100%;
}
.sm-btn:hover:not(:disabled) { opacity: .85; transform: translateY(-1px); }
.sm-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }
.sm-btn-sm { padding: 7px 14px; font-size: 12.5px; width: auto; }
.sm-btn-ghost {
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.7);
    border: 1px solid rgba(255,255,255,.15);
}
.sm-btn-ghost:hover { background: rgba(255,255,255,.15); color: #fff; opacity: 1; }
.sm-btn-submit {
    background: linear-gradient(135deg, var(--sm-teal) 0%, #0c6682 100%);
    color: #fff;
    box-shadow: 0 3px 12px rgba(14,116,144,.35);
    font-size: 13.5px;
    padding: 11px 18px;
}
.sm-btn-teal { background: var(--sm-teal); color: #fff; }
.sm-btn-info { background: var(--sm-amber); color: #1a1a1a; }

/* ── Modal ── */
.sm-overlay {
    position: fixed;
    inset: 0;
    background: rgba(11,31,58,.55);
    z-index: 9000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.sm-overlay.open { display: flex; }
.sm-modal {
    background: var(--sm-white);
    border-radius: var(--sm-radius);
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    width: 100%;
    max-width: 620px;
    max-height: 88vh;
    overflow-y: auto;
    animation: sm-modal-in .2s ease;
}
.sm-modal-head {
    background: var(--sm-navy);
    color: #fff;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 2;
    border-radius: var(--sm-radius) var(--sm-radius) 0 0;
}
.sm-modal-head h4 {
    font-family: 'Playfair Display', serif;
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sm-modal-head h4 i { color: var(--sm-sky); }
.sm-modal-close {
    background: none;
    border: none;
    color: rgba(255,255,255,.65);
    font-size: 22px;
    cursor: pointer;
    transition: color .13s;
    line-height: 1;
}
.sm-modal-close:hover { color: #fff; }
.sm-modal-body { padding: 20px; font-size: 13.5px; line-height: 1.6; }
@keyframes sm-modal-in { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ── Toast ── */
.sm-toast-wrap {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 8px;
    pointer-events: none;
}
.sm-toast {
    padding: 11px 16px;
    border-radius: 10px;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 18px rgba(0,0,0,.2);
    display: flex;
    align-items: center;
    gap: 8px;
    animation: sm-toast-in .22s ease;
    max-width: 300px;
    pointer-events: auto;
    transition: opacity .3s;
}
.sm-toast-hide { opacity: 0; }
.sm-toast-success { background: var(--sm-green); }
.sm-toast-error   { background: var(--sm-red); }
.sm-toast-warning { background: var(--sm-amber); }
.sm-toast-info    { background: var(--sm-teal); }
@keyframes sm-toast-in { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

/* ── Responsive ── */
@media (max-width: 760px) {
    .subject-row { flex-direction: column; gap: 8px; }
    .subject-label { flex: 0 0 auto; }
    .sm-card-body { padding: 14px; }
}
</style>