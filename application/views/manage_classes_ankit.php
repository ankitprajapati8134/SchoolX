<?php
// Initialize pagination variables
$totalClasses = sizeof($Classes); // Total number of classes
$classesPerPage = 12; // Number of classes per page
$totalPages = ceil($totalClasses / $classesPerPage); // Total number of pages
?>


<div class="content-wrapper">
    <div class="page_container">

        <!-- Modern Header Section -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <i class="fa fa-graduation-cap header-icon"></i>
                    <div>
                        <h2 class="page-title">Class Management</h2>
                        <p class="page-subtitle">Manage all classes and sections</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="stats-badge">
                        <span class="stats-number"><?= $totalClasses ?></span>
                        <span class="stats-label">Total Classes</span>
                    </div>
                    <button class="btn btn-add-modern" id="addClass" data-toggle="modal" data-target="#myModal">
                        <i class="fa fa-plus-circle"></i> Add New Class
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Grid Container -->
        <div class="modern-grid-wrapper">
            <div class="grid-container-modern" id="gridContainer">
                <?php
                // Step 1: Separate textual and numeric classes
                $textClasses = [];
                $numericClasses = [];

                foreach ($Classes as $class) {
                    $className = $class['class_name'];
                    if (preg_match('/\d+/', $className, $matches)) {
                        $class['numeric_value'] = intval($matches[0]);
                        $numericClasses[] = $class;
                    } else {
                        $textClasses[] = $class;
                    }
                }

                usort($textClasses, function ($a, $b) {
                    return strcasecmp($a['class_name'], $b['class_name']);
                });

                usort($numericClasses, function ($a, $b) {
                    return $a['numeric_value'] <=> $b['numeric_value'];
                });

                $sortedClasses = array_merge($textClasses, $numericClasses);

                for ($i = 0; $i < min($classesPerPage, $totalClasses); $i++) {
                    $class = $sortedClasses[$i];
                    $classSection = $class['class_name'] . " '" . $class['section'] . "'";
                ?>
                <div class="class-card-modern" data-class-section="<?= urlencode($classSection) ?>">
                    <div class="card-header-modern">
                        <div class="class-icon-wrapper">
                            <i class="fa fa-book"></i>
                        </div>
                        <h4 class="class-title"><?= htmlspecialchars($classSection) ?></h4>
                    </div>

                    <div class="card-body-modern">
                        <div class="class-info-item">
                            <i class="fa fa-users text-muted"></i>
                            <span>Class: <?= htmlspecialchars($class['class_name']) ?></span>
                        </div>
                        <div class="class-info-item">
                            <i class="fa fa-bookmark text-muted"></i>
                            <span>Section: <?= htmlspecialchars($class['section']) ?></span>
                        </div>
                    </div>

                    <div class="card-actions-modern">
                        <button class="btn-action btn-view view-class-btn"
                            data-class-section="<?= urlencode($classSection) ?>" title="View Details">
                            <i class="fa fa-eye"></i>
                            <span>View</span>
                        </button>
                        <button class="btn-action btn-edit edit-class-btn"
                            data-class-name="<?= htmlspecialchars($class['class_name']) ?>"
                            data-section="<?= htmlspecialchars($class['section']) ?>"
                            data-school="<?= htmlspecialchars($selected_school) ?>" data-toggle="modal"
                            data-target="#editClassModal" title="Edit Class">
                            <i class="fa fa-pencil"></i>
                            <span>Edit</span>
                        </button>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>

            <!-- Modern Pagination -->
            <div class="pagination-modern">
                <button class="pagination-btn-modern" id="prevBtn" disabled>
                    <i class="fa fa-chevron-left"></i> Previous
                </button>
                <div class="pagination-info">
                    Page <span id="currentPage">1</span> of <span
                        id="totalPages"><?= ceil($totalClasses / $classesPerPage) ?></span>
                </div>
                <button class="pagination-btn-modern" id="nextBtn"
                    <?= $totalClasses <= $classesPerPage ? 'disabled' : '' ?>>
                    Next <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- MODAL (Keep your existing modal code here with updated styling) -->
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content classy-modal">
                    <!-- Modal Header -->
                    <div class="modal-header text-center">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title w-100">
                            <i class="fa fa-plus-circle text-success"></i> Add New Class
                        </h4>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <form id="subject_form" method="post">
                            <div class="row">
                                <!-- LEFT COLUMN -->
                                <div class="col-md-7 left-column">
                                    <div class="form-row">
                                        <label for="classSelect"><i class="fa fa-graduation-cap"></i> Class</label>
                                        <select class="form-control custom-select" id="classSelect">
                                            <option disabled selected>Select Class</option>
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
                                    </div>

                                    <!-- <div class="form-row">
                                        <label for="sectionSelect"><i class="fa fa-bookmark"></i> Section</label>
                                        <select class="form-control custom-select" id="sectionSelect">
                                            <option disabled selected>Select Section</option>
                                            <option>A</option>
                                            <option>B</option>
                                            <option>C</option>
                                            <option>D</option>
                                            <option>E</option>
                                            <option>F</option>
                                            <option>G</option>
                                            <option>H</option>
                                            <option>I</option>
                                            <option>J</option>
                                            <option>K</option>
                                        </select>
                                    </div> -->
                                    <!-- SUBJECTS will be injected here dynamically -->
                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-md-5 right-column">
                                    <div class="selected-box">
                                        <div>
                                            <div class="selected-header">
                                                <span><i class="fa fa-check-circle"></i> Selected Subjects</span>
                                                <button type="button" class="clear-btn">
                                                    <i class="fa fa-trash"></i> Clear all
                                                </button>
                                            </div>
                                            <div id="selected_subjects">
                                                <!-- Selected subjects will appear here -->
                                            </div>
                                        </div>
                                        <button type="submit" class="save-btn">
                                            <i class="fa fa-save"></i> Save Class
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {

    let languageRules = {}; // Fetched dynamically

    // When class is selected
    $('#classSelect').on('change', function() {
        const selectedClass = $(this).val();
        if (selectedClass === "Select Class") return;

        $.ajax({
            url: "<?= base_url('classes/fetch_subjects') ?>",
            method: 'POST',
            data: {
                class: selectedClass
            },
            dataType: 'json',
            success: function(response) {
                renderSubjects(response);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });





    function renderSubjects(subjectsData) {
        const leftColumn = $('.left-column');

        // 🧹 Clear old UI
        leftColumn.find('.subject-section').remove();
        $('#selected_subjects').empty();
        $('.subject-btn.active').removeClass('active');
        $('.language-dropdown, .subject-dropdown').val('');
        $('.subject-btn[data-dropdown-lang]').remove();

        // 🧾 Reset language rule defaults (safe parse)
        if (typeof languageRules === 'undefined' || languageRules === null) window.languageRules = {};
        languageRules = languageRules || {};
        languageRules.min = parseInt(languageRules.min || 1, 10) || 1;
        languageRules.max = parseInt(languageRules.max || 2, 10) || 2;

        // escapeHtml helper (moved up so assessment handler can use it safely)
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // 📘 Ensure assessment modal exists only once (use the improved template)
        if ($('#assessmentInfoModal').length === 0) {
            $('body').append(`
            <div class="modal fade" id="assessmentInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title"><span style="display:inline-block;margin-right:8px">📘</span><span>Assessment Information &amp; Rules</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>

                    <div class="modal-body">
                    <div id="assessmentInfoContent">
                        <!-- populated dynamically -->
                    </div>

                    <div style="margin-top:14px">
                        <hr style="border:none;border-top:1px solid #eef1f5;margin:12px 0;">
                        <div class="assess-note">
                        <strong>Note:</strong>
                        <div style="margin-top:6px">The pattern is based on CBSE guidelines for all classes. Please consult your school-specific rules for any local variations.</div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        `);
        }

        // 🔧 Helper: remove selected subject by name
        function removeSelectedSubjectByName(name, group, dropdownLang = null) {
            const sel = $('#selected_subjects .selected-subject').filter(function() {
                return $(this).data('group') === group && $(this).data('subject-name') === name;
            }).first();

            if (sel.length) {
                const isDropdown = sel.data('is-dropdown-created');
                const dropdownLangName = sel.data('dropdown-lang');
                sel.remove();

                if (isDropdown && dropdownLangName) {
                    $(`.subject-btn[data-dropdown-lang="${dropdownLangName}"]`).remove();
                } else {
                    // prefer matching by data attribute (safer than .text())
                    $(`.subject-btn`).filter(function() {
                        return $(this).data('group') === group && $(this).data('subject-name') === name;
                    }).removeClass('active');
                }
            }
        }

        // 🔧 Helper: check duplicate subjects
        function isSubjectAlreadySelected(name, group) {
            return $('#selected_subjects .selected-subject').filter(function() {
                return $(this).data('group') === group && $(this).data('subject-name') === name;
            }).length > 0;
        }

        // Helper: normalize a rulesource value into array of strings
        function normalizeToArray(val) {
            if (val === undefined || val === null) return [];
            if (Array.isArray(val)) return val.map(v => v === null || v === undefined ? '' : String(v));
            if (typeof val === 'object') return Object.values(val).map(v => v === null || v === undefined ? '' :
                String(v));
            return [String(val)];
        }

        // Helper: check whether the value is not purely numeric (reject "2", "3.0", etc.)
        function isMeaningfulText(s) {
            if (s === undefined || s === null) return false;
            const str = String(s).trim();
            if (str === '') return false;
            // reject plain numbers (integer or decimal), e.g. "2", "3.0"
            if (/^[+-]?\d+(\.\d+)?$/.test(str)) return false;
            return true;
        }

        // 🧩 Render subjects from fetched data
        $.each(subjectsData, function(group, data) {

            // 🟦 Handle Assessment section dynamically
            if (group === "Assessment") {
                // handler factory: returns a click handler bound to current data + subjectsData
                function makeAssessmentClickHandler(data, subjectsData) {
                    return function(e) {
                        // ensure no default/other handlers run
                        if (e && typeof e.preventDefault === 'function') {
                            e.preventDefault();
                            e.stopPropagation();
                        }

                        // ---------- Assessment Scheme (unchanged, but uses escapeHtml safely) ----------
                        const schemeItems = [];
                        if (data.grading_scale) schemeItems.push(
                            `<li><strong>Grading Scale:</strong> ${escapeHtml(String(data.grading_scale))}</li>`
                        );
                        if (data.internal_assessment_marks !== undefined) schemeItems.push(
                            `<li><strong>Internal Assessment Marks:</strong> ${escapeHtml(String(data.internal_assessment_marks))}</li>`
                        );
                        if (data.theory_marks !== undefined) schemeItems.push(
                            `<li><strong>Theory Marks:</strong> ${escapeHtml(String(data.theory_marks))}</li>`
                        );
                        if (Array.isArray(data.observation_areas) && data.observation_areas.length)
                            schemeItems.push(
                                `<li><strong>Observation Areas:</strong> ${data.observation_areas.map(x => escapeHtml(String(x))).join(', ')}</li>`
                            );

                        for (const k in data) {
                            if (['grading_scale', 'internal_assessment_marks', 'theory_marks',
                                    'observation_areas'
                                ].includes(k)) continue;
                            if (k.toLowerCase() === 'note') continue;
                            const v = data[k];
                            if (typeof v === 'string' || typeof v === 'number' || typeof v ===
                                'boolean') {
                                schemeItems.push(
                                    `<li><strong>${escapeHtml(k)}:</strong> ${escapeHtml(String(v))}</li>`
                                );
                            } else if (Array.isArray(v)) {
                                schemeItems.push(
                                    `<li><strong>${escapeHtml(k)}:</strong> ${v.map(x => escapeHtml(String(x))).join(', ')}</li>`
                                );
                            } else if (typeof v === 'object' && v !== null) {
                                schemeItems.push(
                                    `<li><strong>${escapeHtml(k)}:</strong> ${escapeHtml(JSON.stringify(v))}</li>`
                                );
                            }
                        }

                        const schemeHtml = schemeItems.length ?
                            `<ul class="mb-2">${schemeItems.join('')}</ul>` :
                            `<p class="mb-2"><em>No assessment scheme information available.</em></p>`;

                        // ---------- Rules parsing (keeps your existing logic) ----------
                        const normKey = k => String(k).replace(/[^a-z0-9]+/gi, '_').replace(
                            /^_+|_+$/g, '').toLowerCase();
                        const titleKey = t => String(t).replace(/[^a-z0-9]+/gi, ' ').replace(/\s+/g,
                            ' ').trim().toLowerCase();
                        const addUnique = (arr, items) => {
                            for (const it of items)
                                if (!arr.includes(it)) arr.push(it);
                        };

                        const toStrings = raw => {
                            if (raw == null) return [];
                            if (Array.isArray(raw)) return raw.map(x => String(x)).map(s => s
                                .trim()).filter(Boolean);
                            if (typeof raw === 'object') {
                                if (Object.prototype.hasOwnProperty.call(raw, 'rules'))
                                    return toStrings(raw.rules);
                                const keys = Object.keys(raw);
                                const numeric = keys.filter(k => /^\d+$/.test(k)).sort((a, b) =>
                                    Number(a) - Number(b));
                                if (numeric.length) return numeric.map(k => raw[k]).map(x =>
                                    String(x)).map(s => s.trim()).filter(Boolean);
                                return Object.values(raw).map(x => String(x)).map(s => s.trim())
                                    .filter(Boolean);
                            }
                            return [String(raw).trim()].filter(Boolean);
                        };

                        const isSelectMetaKey = key => {
                            const k = normKey(key);
                            return k.endsWith('_max_select') || k.endsWith('_min_select') || k
                                .includes('max_select') || k.includes('min_select');
                        };

                        // categoryMap for one-pass merging (normalizes titles)
                        const categoryMap = new Map();

                        function addCategory(title, vals) {
                            if (!title || !vals || !vals.length) return;
                            const nk = titleKey(title);
                            if (!categoryMap.has(nk)) {
                                const uniqVals = [];
                                for (const v of vals)
                                    if (!uniqVals.includes(v)) uniqVals.push(v);
                                categoryMap.set(nk, {
                                    displayTitle: title,
                                    values: uniqVals
                                });
                            } else {
                                const entry = categoryMap.get(nk);
                                addUnique(entry.values, vals);
                            }
                        }

                        // gather rulesSource
                        let rulesSource = null;
                        if (subjectsData && subjectsData.Core && subjectsData.Core.rules !==
                            undefined) {
                            rulesSource = subjectsData.Core.rules;
                        } else if (subjectsData && subjectsData.rules !== undefined) {
                            rulesSource = subjectsData.rules;
                        }

                        const general = [];

                        // collect category rules under subjectsData.Core.<Category>.rules
                        if (subjectsData && subjectsData.Core && typeof subjectsData.Core ===
                            'object') {
                            Object.entries(subjectsData.Core).forEach(([k, v]) => {
                                if (v && typeof v === 'object' && v.rules !== undefined) {
                                    const title = (k === 'Core' || k.toLowerCase() ===
                                        'rules') ? null : k;
                                    const vals = toStrings(v.rules);
                                    if (!vals.length) return;
                                    if (title) addCategory(title, vals);
                                    else addUnique(general, vals);
                                }
                            });
                        }

                        // merge nested rules if present
                        if (rulesSource && typeof rulesSource === 'object' && Object.prototype
                            .hasOwnProperty.call(rulesSource, 'rules')) {
                            const nested = rulesSource.rules;
                            if (Array.isArray(nested)) addUnique(general, toStrings(nested));
                            else if (nested && typeof nested === 'object') rulesSource = Object
                                .assign({}, rulesSource, nested);
                            else addUnique(general, toStrings(nested));
                        }

                        // process rulesSource
                        if (Array.isArray(rulesSource)) {
                            addUnique(general, toStrings(rulesSource));
                        } else if (rulesSource && typeof rulesSource === 'object') {
                            Object.entries(rulesSource).forEach(([rawKey, rawVal]) => {
                                if (isSelectMetaKey(rawKey)) return;
                                const key = String(rawKey);
                                const k = normKey(key);
                                const vals = toStrings(rawVal);
                                if (!vals.length) return;
                                if (/^\d+$/.test(key) || k === 'rules' || k.includes(
                                        'core')) {
                                    addUnique(general, vals);
                                    return;
                                }
                                if (/_?rules?$/.test(k) || k.includes('rule')) {
                                    let title = k.replace(/_?rules?$/i, '').replace(/_/g,
                                        ' ').trim();
                                    if (!title) title = key;
                                    title = title.split(/\s+/).map(w => w.charAt(0)
                                        .toUpperCase() + w.slice(1)).join(' ');
                                    addCategory(title, vals);
                                    return;
                                }
                            });
                        }

                        // also pick up top-level subjectsData.<Category>.rules if present
                        if (subjectsData && typeof subjectsData === 'object') {
                            Object.entries(subjectsData).forEach(([topKey, topVal]) => {
                                if (topKey === 'Core' || topKey === 'rules') return;
                                if (topVal && typeof topVal === 'object' && topVal.rules !==
                                    undefined) {
                                    addCategory(topKey, toStrings(topVal.rules));
                                }
                            });
                        }

                        // build merged categories in insertion order
                        const mergedCategories = [];
                        for (const [nk, {
                                displayTitle,
                                values
                            }] of categoryMap.entries()) {
                            mergedCategories.push({
                                title: displayTitle,
                                values: values
                            });
                        }

                        // dedupe general rules
                        const dedupGeneral = [];
                        for (const r of general)
                            if (!dedupGeneral.includes(r)) dedupGeneral.push(r);

                        // build rulesHtml (bulleted)
                        const parts = [];
                        if (dedupGeneral.length) {
                            parts.push(
                                `<div class="mb-2"><strong>General rules:</strong><ul class="mb-1">${dedupGeneral.map(r => `<li>${escapeHtml(r)}</li>`).join('')}</ul></div>`
                            );
                        }
                        mergedCategories.forEach(cat => {
                            if (!cat.values.length) return;
                            parts.push(
                                `<div class="mb-2"><strong>For ${escapeHtml(cat.title)}:</strong><ul class="mb-1">${cat.values.map(v => `<li>${escapeHtml(v)}</li>`).join('')}</ul></div>`
                            );
                        });
                        if (!parts.length) parts.push(
                            `<p class="mb-2"><em>No rules available for this class.</em></p>`);
                        const rulesHtml = parts.join('');

                        // Note handling (unchanged)
                        let note = null;
                        if (data && data.note) note = data.note;
                        else if (subjectsData && subjectsData.Core && subjectsData.Core.note) note =
                            subjectsData.Core.note;
                        else if (subjectsData && subjectsData.note) note = subjectsData.note;
                        const noteHtml = note ?
                            `<div class="mt-2"><strong>Note:</strong><div>${escapeHtml(String(note))}</div></div>` :
                            '';

                        // Final modal HTML
                        const html = `
                        <div>
                            <div class="mb-2">
                                <strong>Assessment Scheme:</strong>
                                ${schemeHtml}
                            </div>

                            <div class="mb-2">
                                <strong>Rules:</strong>
                                ${rulesHtml}
                            </div>

                            ${noteHtml}
                        </div>
                    `;

                        $('#assessmentInfoContent').html(html);
                        $('#assessmentInfoModal').modal('show');
                    };
                } // end makeAssessmentClickHandler

                // find existing assessment-info area (if any)
                const existing = leftColumn.find('.assessment-info').first();
                const clickHandler = makeAssessmentClickHandler(data, subjectsData);

                if (existing.length) {
                    // update its button handler (rebind) so it uses current data
                    const btn = existing.find('button.info-btn');
                    if (btn.length) {
                        btn.off('click').on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            clickHandler(e);
                        });
                    } else {
                        // if no button inside (unlikely), append one
                        const newBtn = $('<button type="button">').addClass(
                            'info-btn btn btn-sm btn-outline-info').html('ℹ️').on('click', function(
                            e) {
                            e.preventDefault();
                            e.stopPropagation();
                            clickHandler(e);
                        });
                        existing.append(newBtn);
                    }
                } else {
                    // create it once (use type="button" and prevent default on click)
                    const infoBtn = $('<button type="button">').addClass(
                        'info-btn btn btn-sm btn-outline-info').html('ℹ️').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        clickHandler(e);
                    });
                    leftColumn.prepend(
                        $('<div>').addClass('assessment-info mb-3 d-flex align-items-center')
                        .append('<strong class="me-2">Assessment Information &amp; Rules</strong>')
                        .append(infoBtn)
                    );
                }

                return; // Skip rendering as regular group
            } // end Assessment branch

            // 🟨 Subject group UI setup
            const sectionDiv = $('<div>').addClass('subject-section mb-3').attr('data-group', group);
            const rowDiv = $('<div>').addClass('subject-row');
            const labelDiv = $('<div>').addClass('subject-label').html(`<strong>${group}</strong>`);
            const buttonsDiv = $('<div>').addClass('subject-buttons');

            let subjects = [];
            let rules = {};
            let isCompulsory = false;

            // ✅ Parse data safely
            if (typeof data === 'object' && data.options) {
                if (Array.isArray(data.options)) {
                    subjects = data.options.slice();
                } else if (typeof data.options === 'object') {
                    subjects = Object.values(data.options).slice();
                }
                rules = data.rules || {};
                isCompulsory = data.compulsory === true;
            } else if (Array.isArray(data)) {
                subjects = data.slice();
            }

            // Special-case: for "Additional" group we still want to render even if subjects is empty
            if (group !== 'Additional' && (!subjects || subjects.length === 0)) return;

            // 📝 Info text for category
            const infoText = $('<small>')
                .addClass('text-muted d-block mt-1')
                .text(
                    isCompulsory ?
                    'Compulsory (as per CBSE pattern)' :
                    (rules && rules.min_select ?
                        `Select minimum ${rules.min_select} and maximum ${rules.max_select || 'any'} subjects` :
                        'Optional subjects - select as needed.')
                );

            // 🟢 Languages
            if (group === "Languages") {
                languageRules.min = parseInt(rules.min_select || rules.min || languageRules.min || 1,
                    10) || 1;
                languageRules.max = parseInt(rules.max_select || rules.max || languageRules.max || 2,
                    10) || 2;

                const englishBtn = $('<button>')
                    .attr('type', 'button')
                    .addClass('subject-btn')
                    .text('English')
                    .data('group', 'Languages')
                    .data('subject-name', 'English')
                    .on('click', function() {
                        const btn = $(this);
                        const name = 'English';
                        if (btn.hasClass('active')) {
                            removeSelectedSubjectByName(name, 'Languages');
                            btn.removeClass('active');
                        } else {
                            if (!isSubjectAlreadySelected(name, 'Languages')) {
                                selectSubject(name, btn, 'Languages');
                                btn.addClass('active');
                            }
                        }
                        if (typeof updateLanguageGroupWarning === 'function')
                            updateLanguageGroupWarning();
                    });

                const dropdown = $('<select>')
                    .addClass('form-control custom-select language-dropdown')
                    .append($('<option>').val('').text('Select Other Language'));

                subjects.forEach(sub => {
                    if (sub !== 'English') dropdown.append($('<option>').val(sub).text(sub));
                });

                dropdown.on('change', function() {
                    const selectedLang = $(this).val();
                    if (!selectedLang) return;

                    const totalSelected = $('#selected_subjects .selected-subject')
                        .filter((i, el) => $(el).data('group') === 'Languages').length;

                    if (totalSelected >= languageRules.max) {
                        alert(`You can select maximum ${languageRules.max} languages.`);
                        $(this).val('');
                        return;
                    }

                    if (isSubjectAlreadySelected(selectedLang, 'Languages')) {
                        $(this).val('');
                        return;
                    }

                    const langBtn = $('<button>')
                        .attr('type', 'button')
                        .addClass('subject-btn active')
                        .text(selectedLang)
                        .data('group', 'Languages')
                        .data('subject-name', selectedLang)
                        .attr('data-dropdown-lang', selectedLang)
                        .on('click', function() {
                            const b = $(this);
                            const nm = $(this).data('subject-name') || $(this).text();
                            if (b.hasClass('active')) {
                                removeSelectedSubjectByName(nm, 'Languages', nm);
                                b.removeClass('active');
                            } else {
                                selectSubject(nm, b, 'Languages', nm);
                                b.addClass('active');
                            }
                        });

                    $(this).before(langBtn);
                    selectSubject(selectedLang, langBtn, 'Languages', selectedLang);
                    $(this).val('');
                    if (typeof updateLanguageGroupWarning === 'function')
                        updateLanguageGroupWarning();
                });

                buttonsDiv.append(englishBtn).append(dropdown).append(infoText);
            }

            // 🔵 Compulsory groups
            else if (isCompulsory) {
                subjects.forEach(subject => {
                    const btn = $('<button>')
                        .attr('type', 'button')
                        .addClass('subject-btn active')
                        .text(subject)
                        .data('group', group)
                        .data('subject-name', subject)
                        .on('click', function() {
                            const b = $(this);
                            const nm = $(this).data('subject-name') || $(this).text();
                            if (b.hasClass('active')) {
                                removeSelectedSubjectByName(nm, group);
                                b.removeClass('active');
                            } else {
                                if (!isSubjectAlreadySelected(nm, group)) {
                                    selectSubject(nm, btn, group);
                                    btn.addClass('active');
                                }
                            }
                        });
                    buttonsDiv.append(btn);
                    if (!isSubjectAlreadySelected(subject, group)) selectSubject(subject, btn,
                        group);
                });
                buttonsDiv.append(infoText);
            }


            // 🟣 Optional groups
            else {
                // DEBUG: log group + subjects so we can confirm what's being rendered
                console.log('renderSubjects: optional group=', group, 'subjects=', subjects);

                // clear any leftover dropdown in this buttonsDiv (defensive)
                buttonsDiv.find('.subject-dropdown').remove();

                // If this is the "Additional" category -> special UI: dropdown only if options exist + text input & Add button
                if (group === 'Additional') {
                    // Clean options: remove falsy / non-meaningful entries (so `false` won't create a dropdown)
                    let dropdown = null;
                    const cleanedOptions = Array.isArray(subjects) ?
                        subjects
                        .map(s => (s === null || s === undefined) ? '' : String(s).trim())
                        .filter(s => s !== '' && s.toLowerCase() !==
                        'false') // remove empty strings and literal "false" string
                        :
                        [];

                    // If you prefer to allow the literal word "false" as a subject, remove the toLowerCase() check above.
                    if (cleanedOptions.length > 0) {
                        dropdown = $('<select>')
                            .addClass('form-control custom-select subject-dropdown')
                            .append($('<option>').val('').text('Select Subject'));

                        cleanedOptions.forEach(sub => dropdown.append($('<option>').val(sub).text(
                        sub)));
                    } else {
                        console.log(
                        'Additional has NO options after cleaning — not creating dropdown.');
                    }


                    // create input + button for custom subject entry
                    const inputGroup = $('<div>').addClass('additional-input-group d-flex mt-2').css(
                        'gap', '6px');
                    const customInput = $('<input>')
                        .attr('type', 'text')
                        .addClass('form-control additional-subject-input')
                        .attr('placeholder', 'Type a custom subject and press Enter or Add');
                    const addBtn = $('<button>')
                        .attr('type', 'button')
                        .addClass('btn btn-sm btn-primary add-additional-btn')
                        .text('Add');

                    inputGroup.append(customInput).append(addBtn);

                    // Helper to create a subject button and select it
                    function createAndSelectSubject(name) {
                        name = String(name || '').trim();
                        if (!isMeaningfulText(name)) {
                            alert('Please enter a valid subject name (non-empty, not just a number).');
                            return;
                        }
                        if (isSubjectAlreadySelected(name, group)) return;

                        const btn = $('<button>')
                            .attr('type', 'button')
                            .addClass('subject-btn active')
                            .text(name)
                            .data('group', group)
                            .data('subject-name', name)
                            .data('is-dropdown-created', true)
                            .attr('data-dropdown-lang', name)
                            .on('click', function() {
                                const b = $(this);
                                const nm = $(this).data('subject-name') || $(this).text();
                                if (b.hasClass('active')) {
                                    removeSelectedSubjectByName(nm, group, nm);
                                    b.removeClass('active');
                                } else {
                                    selectSubject(nm, b, group, nm);
                                    b.addClass('active');
                                }
                            });

                        // insert button before dropdown if dropdown exists in DOM, otherwise append to buttonsDiv
                        if (dropdown && dropdown.parent().length) dropdown.before(btn);
                        else buttonsDiv.append(btn);

                        // call existing selection handler
                        selectSubject(name, btn, group, name);

                        // if dropdown exists, add the new subject as an option (avoid duplicate)
                        if (dropdown) {
                            if (dropdown.find(`option[value="${name}"]`).length === 0) {
                                dropdown.append($('<option>').val(name).text(name));
                            }
                        }

                        // keep local subjects list in sync
                        if (!subjects.includes(name)) subjects.push(name);

                        // clear input
                        customInput.val('');
                    }

                    // wire Add button click
                    addBtn.on('click', function() {
                        const v = customInput.val();
                        createAndSelectSubject(v);
                    });

                    // wire Enter key on input
                    customInput.on('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            createAndSelectSubject($(this).val());
                        }
                    });

                    // wire dropdown change (if dropdown exists)
                    if (dropdown) {
                        dropdown.on('change', function() {
                            const selectedSub = $(this).val();
                            if (!selectedSub) return;
                            if (isSubjectAlreadySelected(selectedSub, group)) {
                                $(this).val('');
                                return;
                            }

                            const btn = $('<button>')
                                .attr('type', 'button')
                                .addClass('subject-btn active')
                                .text(selectedSub)
                                .data('group', group)
                                .data('subject-name', selectedSub)
                                .data('is-dropdown-created', true)
                                .attr('data-dropdown-lang', selectedSub)
                                .on('click', function() {
                                    const b = $(this);
                                    const nm = $(this).data('subject-name') || $(this)
                                        .text();
                                    if (b.hasClass('active')) {
                                        removeSelectedSubjectByName(nm, group, nm);
                                        b.removeClass('active');
                                    } else {
                                        selectSubject(nm, b, group, nm);
                                        b.addClass('active');
                                    }
                                });

                            $(this).before(btn);
                            selectSubject(selectedSub, btn, group, selectedSub);
                            $(this).val('');
                        });

                        // Add dropdown + input if dropdown exists
                        buttonsDiv.append(dropdown);
                        buttonsDiv.append(inputGroup);
                    } else {
                        // no dropdown -> only show input + button
                        buttonsDiv.append(inputGroup);
                    }

                    buttonsDiv.append(infoText);
                } else {
                    // default optional behavior for other groups (unchanged logic but safe option creation)
                    const dropdown = $('<select>')
                        .addClass('form-control custom-select subject-dropdown')
                        .append($('<option>').val('').text('Select Subject'));

                    subjects.forEach(sub => dropdown.append($('<option>').val(sub).text(sub)));

                    dropdown.on('change', function() {
                        const selectedSub = $(this).val();
                        if (!selectedSub) return;
                        if (isSubjectAlreadySelected(selectedSub, group)) {
                            $(this).val('');
                            return;
                        }

                        const btn = $('<button>')
                            .attr('type', 'button')
                            .addClass('subject-btn active')
                            .text(selectedSub)
                            .data('group', group)
                            .data('subject-name', selectedSub)
                            .data('is-dropdown-created', true)
                            .attr('data-dropdown-lang', selectedSub)
                            .on('click', function() {
                                const b = $(this);
                                const nm = $(this).data('subject-name') || $(this).text();
                                if (b.hasClass('active')) {
                                    removeSelectedSubjectByName(nm, group, nm);
                                    b.removeClass('active');
                                } else {
                                    selectSubject(nm, b, group, nm);
                                    b.addClass('active');
                                }
                            });

                        $(this).before(btn);
                        selectSubject(selectedSub, btn, group, selectedSub);
                        $(this).val('');
                    });

                    buttonsDiv.append(dropdown).append(infoText);
                }
            }


            // 🧱 Append section
            rowDiv.append(labelDiv).append(buttonsDiv);
            sectionDiv.append(rowDiv);
            leftColumn.append(sectionDiv);
        });
    }




    // Select subject
    function selectSubject(subject, btn, group, dropdownLangName = null) {
        const selectedContainer = $('#selected_subjects');

        // Prevent duplicates
        if ($(`#selected_subjects .selected-subject span:contains("${subject}")`).length) return;

        const div = $('<div>')
            .addClass('selected-subject')
            .html(`<span>${subject}</span><button type="button" class="remove-btn">&times;</button>`)
            .data('group', group)
            .data('subject-name', subject)
            .data('is-dropdown-created', !!dropdownLangName)
            .data('dropdown-lang', dropdownLangName)
            .data('btn-element', btn);

        // Remove subject manually
        div.find('.remove-btn').on('click', function() {
            const isDropdownCreated = div.data('is-dropdown-created');
            const dropdownLang = div.data('dropdown-lang');
            const originalBtn = div.data('btn-element');
            const subjectGroup = div.data('group');

            div.remove();

            // If it was from dropdown, delete its dynamically created button
            if (isDropdownCreated && dropdownLang) {
                $(`.subject-btn[data-dropdown-lang="${dropdownLang}"]`).remove();
            } else {
                // For regular buttons (like English), just remove active class
                originalBtn.removeClass('active');
            }

            // ✅ Only update warning if it's a Language subject
            if (subjectGroup === 'Languages') {
                updateLanguageGroupWarning();
            }
        });

        selectedContainer.append(div);
        btn.addClass('active');

        // ✅ Don't call updateLanguageGroupWarning here - it's called by the click handler
    }




    // Red box warning for language group
    function updateLanguageGroupWarning() {
        const languagesSelected = $('#selected_subjects .selected-subject')
            .filter((i, el) => $(el).data('group') === 'Languages').length;

        const languagesGroupDiv = $('.subject-section[data-group="Languages"]');
        if (languagesGroupDiv.length) {
            if (languagesSelected > 0 && languagesSelected < languageRules.min) {
                // ✅ Only show warning if at least 1 language is selected but less than minimum
                languagesGroupDiv.addClass('group-warning');
            } else {
                languagesGroupDiv.removeClass('group-warning');
            }
        }
    }

    // Clear all button
    $('.clear-btn').on('click', function() {
        $('#selected_subjects').empty();
        $('.subject-btn.active').removeClass('active');
        $('.language-dropdown').val('');
        $('.subject-btn[data-dropdown-lang]').remove();
        updateLanguageGroupWarning();
    });

    // Form submit validation
    $('#subject_form').on('submit', function(e) {
        const languagesSelected = $('#selected_subjects .selected-subject')
            .filter((i, el) => $(el).data('group') === 'Languages').length;

        if (languagesSelected < languageRules.min) {
            e.preventDefault();
            alert(`Please select at least ${languageRules.min} languages.`);
            updateLanguageGroupWarning();
        }
    });

});


function attachEventListeners() {
    $(".view-class-btn").off("click").on("click", function(event) {
        event.stopPropagation(); // Prevent parent click event
        var encodedClassSection = $(this).data("class-section");
        window.location.href = "class_profile?class_name=" + encodedClassSection;
    });

    $(".edit-class-btn").off("click").on("click", function(event) {
        event.stopPropagation();

        var rawClassName = $(this).data("class-name"); // Original class name
        var section = $(this).data("section");
        var school = $(this).data("school");

        // ✅ Remove "Class " prefix if it exists
        var className = rawClassName.replace(/^Class\s+/i, "").trim();

        // ✅ Handle cases like "8th", "10th", "Nursery", etc.
        className = className.replace(/(\d+)(st|nd|rd|th)/i, "$1");

        // Populate form fields
        $("#prev_class_name").val(className);
        $("#prev_section").val(section);
        $("#edit_class_name").val(className);
        $("#edit_section").val(section);
        $("#edit_school_name").val(school);

        // Fetch class details from Firebase
        $.ajax({
            url: "<?= base_url('classes/get_class_details') ?>",
            type: "POST",
            data: {
                school_name: school,
                class_name: className,
                section: section
            },
            dataType: "json",
            success: function(data) {
                if (data) {
                    // Load subjects
                    $("#edit_subjects_list").empty();
                    if (Array.isArray(data.subjects)) {
                        data.subjects.forEach(subject => {
                            if (subject !== "class") {
                                $("#edit_subjects_list").append(
                                    `<li>${subject} <button class='btn btn-sm btn-danger remove-subject'>x</button></li>`
                                );
                            }
                        });
                    }

                    // Load optional subjects
                    $("#edit_optional_subjects_list").empty();
                    if (Array.isArray(data.optionalSubjects)) {
                        data.optionalSubjects.forEach(subject => {
                            $("#edit_optional_subjects_list").append(
                                `<li>${subject} <button class='btn btn-sm btn-danger remove-optional-subject'>x</button></li>`
                            );
                        });
                    }

                    // Load timetable
                    if (data.timetable) {
                        $("#edit_timetable_link").html(
                            `<a href="${data.timetable}" target="_blank" class="btn btn-link">View Previous Timetable</a>`
                        );
                    } else {
                        $("#edit_timetable_link").html("<p>No file uploaded</p>");
                    }
                }
            }

        });

        $("#editClassModal").modal("show");
    });
}

// Attach event listeners initially
attachEventListeners();


const classes = <?= json_encode($sortedClasses) ?>; // Sorted classes
const classesPerPage = <?= $classesPerPage ?>; // Number of classes per page
const totalPages = <?= $totalPages ?>; // Total number of pages
let currentPage = 1;

const gridContainer = document.getElementById("gridContainer");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

function loadClasses(page) {
    const startIndex = (page - 1) * classesPerPage;
    const endIndex = Math.min(startIndex + classesPerPage, classes.length);

    // Use the same modern card HTML structure as your server-side/PHP code
    const newContent = classes.slice(startIndex, endIndex).map(classData => {
        const classSection = `${classData.class_name} '${classData.section}'`;
        return `
            <div class="class-card-modern" data-class-section="${encodeURIComponent(classSection)}">
                <div class="card-header-modern">
                    <div class="class-icon-wrapper">
                        <i class="fa fa-book"></i>
                    </div>
                    <h4 class="class-title">${classSection}</h4>
                </div>

                <div class="card-body-modern">
                    <div class="class-info-item">
                        <i class="fa fa-users text-muted"></i>
                        <span>Class: ${classData.class_name}</span>
                    </div>
                    <div class="class-info-item">
                        <i class="fa fa-bookmark text-muted"></i>
                        <span>Section: ${classData.section}</span>
                    </div>
                </div>

                <div class="card-actions-modern">
                    <button class="btn-action btn-view view-class-btn"
                        data-class-section="${encodeURIComponent(classSection)}" title="View Details">
                        <i class="fa fa-eye"></i>
                        <span>View</span>
                    </button>
                    <button class="btn-action btn-edit edit-class-btn"
                        data-class-name="${classData.class_name}"
                        data-section="${classData.section}"
                        data-school="<?= htmlspecialchars($selected_school) ?>"
                        data-toggle="modal"
                        data-target="#editClassModal" title="Edit Class">
                        <i class="fa fa-pencil"></i>
                        <span>Edit</span>
                    </button>
                </div>
            </div>
        `;
    }).join("");

    // Slide animation remains unchanged
    gridContainer.classList.add("sliding");
    gridContainer.style.transition = "transform 0.5s ease-in-out";
    gridContainer.style.transform = page > currentPage ? "translateX(-100%)" : "translateX(100%)";

    setTimeout(() => {
        gridContainer.innerHTML = newContent;
        gridContainer.style.transform = "translateX(0)";
        currentPage = page;
        gridContainer.classList.remove("sliding");
        updateButtons();
        attachEventListeners();
    }, 500);
}

function updateButtons() {
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
}

prevBtn.addEventListener("click", () => {
    if (currentPage > 1) {
        loadClasses(currentPage - 1);
    }
});

nextBtn.addEventListener("click", () => {
    if (currentPage < totalPages) {
        loadClasses(currentPage + 1);
    }
});

// Initial button state
updateButtons();
</script>








<style>
/* ========== Your existing page styles (kept as-is) ========== */
.page-header-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
    color: white;
}

.header-icon {
    font-size: 48px;
    opacity: 0.9;
}

.page-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: white;
}

.page-subtitle {
    margin: 5px 0 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.stats-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 15px 25px;
    border-radius: 10px;
    text-align: center;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.stats-number {
    display: block;
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
}

.stats-label {
    display: block;
    font-size: 12px;
    margin-top: 5px;
    opacity: 0.9;
}

.btn-add-modern {
    background: white;
    color: #667eea;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-add-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    color: #667eea;
}

.btn-add-modern i {
    margin-right: 8px;
}

/* ============================================
           MODERN GRID LAYOUT
           ============================================ */
.modern-grid-wrapper {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 12px;
}

.grid-container-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

.class-card-modern {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.class-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.card-header-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    text-align: center;
    position: relative;
}

.class-icon-wrapper {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    backdrop-filter: blur(10px);
}

.class-icon-wrapper i {
    font-size: 28px;
    color: white;
}

.class-title {
    color: white;
    font-size: 20px;
    font-weight: 600;
    margin: 0;
}

.card-body-modern {
    padding: 20px;
}

.class-info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 14px;
    color: #495057;
}

.class-info-item:last-child {
    margin-bottom: 0;
}

.class-info-item i {
    width: 20px;
    text-align: center;
    color: #6c757d;
}

.card-actions-modern {
    display: flex;
    border-top: 1px solid #e9ecef;
}

.btn-action {
    flex: 1;
    padding: 12px;
    border: none;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
}

.btn-action:hover {
    background: #f8f9fa;
}

.btn-view {
    color: #28a745;
    border-right: 1px solid #e9ecef;
}

.btn-view:hover {
    background: #d4edda;
}

.btn-edit {
    color: #ffc107;
}

.btn-edit:hover {
    background: #fff3cd;
}

.info-btn {
    background: #ffc107;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 0.9em;
    padding: 2px 6px;
    margin-left: 8px;
}

/* ============================================
           MODERN PAGINATION
           ============================================ */
.pagination-modern {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    padding: 20px 0;
}

.pagination-btn-modern {
    background: white;
    border: 1px solid #dee2e6;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #495057;
}

.pagination-btn-modern:hover:not(:disabled) {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-2px);
}

.pagination-btn-modern:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
}

.pagination-info span {
    color: #667eea;
    font-weight: 700;
}

/* ============================================
           MODAL IMPROVEMENTS (UPDATED FOR SCROLLING)
           ============================================ */

/* Modal container - allow internal scrolling, not hiding overflow */
.classy-modal {
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    /* allow content within the modal to scroll instead of hiding it */
    overflow: visible;
    border: none;
}

/* Make modal-body scrollable when content exceeds viewport height.
           Keeps header/footer fixed while body scrolls. Adjust calc() if needed. */
#myModal .modal-body {
    max-height: calc(100vh - 220px);
    /* viewport minus header/footer margins */
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    /* smoother scrolling on mobile */
}

/* If left-column grows tall, let it layout naturally but not push modal beyond viewport */
#myModal .left-column {
    /* allow internal overflow if needed */
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

/* Keep the dialog from overflowing the viewport horizontally and slightly increase width on larger screens */
#myModal .modal-dialog {
    max-width: 920px;
    /* optional, matches modal-lg but can be tweaked */
    margin: 30px auto;
}

.classy-modal .modal-header {
    border-bottom: 2px solid #e9ecef;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 25px 30px;
    position: relative;
}

.classy-modal .modal-title {
    font-weight: 700;
    font-size: 1.5rem;
    color: #333;
}

.classy-modal .close {
    color: #555;
    font-size: 2rem;
    opacity: 0.8;
    position: absolute;
    right: 20px;
    top: 15px;
    transition: 0.2s;
}

.classy-modal .close:hover {
    color: #000;
    opacity: 1;
}

/* Form Styling */
.form-row {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.form-row label {
    flex: 0 0 120px;
    font-weight: 600;
    color: #495057;
    text-align: left;
}

.form-row label i {
    margin-right: 8px;
    color: #667eea;
}

.form-row select {
    flex: 1;
    max-width: 250px;
    border-radius: 8px;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    transition: all 0.2s ease;
}

.form-row select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

/* Subject Section Styling */
.subject-section {
    border-bottom: 1px solid #e9ecef;
    padding: 15px 10px;
    transition: all 0.3s ease-in-out;
    margin-bottom: 10px;
    border-radius: 8px;
}

.subject-section:hover {
    background: #f8f9fa;
}

.subject-row {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.subject-label {
    flex: 0 0 150px;
    text-align: left;
    padding-top: 8px;
    font-weight: 600;
    color: #495057;
}

.subject-buttons {
    flex: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.subject-btn {
    background: #e9ecef;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 16px;
    cursor: pointer;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.2s ease-in-out;
    color: #495057;
}

.subject-btn:hover {
    background: #667eea;
    color: #fff;
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.subject-btn.active {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
    border-color: #28a745;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

/* Languages Group Warning */
.group-warning {
    border: 2px solid #dc3545;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
    padding: 15px;
    border-radius: 12px;
    animation: pulse-warning 1.5s ease-in-out infinite;
}

@keyframes pulse-warning {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }

    50% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
}

/* Selected Box Styling */
.selected-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    min-height: 500px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 2px solid #dee2e6;
}

.selected-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #dee2e6;
}

.selected-header span {
    font-weight: 700;
    font-size: 16px;
    color: #495057;
}

.selected-header span i {
    margin-right: 8px;
    color: #28a745;
}

.clear-btn {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
}

.clear-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.clear-btn i {
    margin-right: 5px;
}

.selected-subject {
    border-radius: 8px;
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
    border-color: #28a745;
}

.selected-subject:hover {
    background: #fff3cd;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.selected-subject span {
    font-size: 14px;
    font-weight: 500;
    color: #495057;
}

#selected_subjects {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 350px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Custom Scrollbar */
#selected_subjects::-webkit-scrollbar {
    width: 6px;
}

#selected_subjects::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#selected_subjects::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

.remove-btn {
    border: none;
    background: transparent;
    color: #dc3545;
    font-size: 20px;
    font-weight: bold;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0 5px;
}

.remove-btn:hover {
    color: #c82333;
    transform: scale(1.3) rotate(90deg);
}

.save-btn {
    background: green;
    color: #fff;
    border: none;
    border-radius: 10px;
    width: 100%;
    padding: 14px;
    font-weight: 700;
    font-size: 15px;
    margin-top: 15px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

/* ---------- Assessment Info Modal (Refined Typography & Layout) ---------- */

/* Container & dialog */
#assessmentInfoModal .modal-dialog {
    max-width: 720px;
    /* balanced width */
    margin: 40px auto;
}

#assessmentInfoModal .modal-content {
    border-radius: 14px;
    overflow: hidden;
    border: none;
    box-shadow: 0 12px 40px rgba(34, 41, 47, 0.18);
    background: #ffffff;
    font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
    font-size: 1.05rem;
    /* comfortable text size */
    line-height: 1.65;
}

/* Header */
#assessmentInfoModal .modal-header {
    padding: 18px 26px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-bottom: none;
}

#assessmentInfoModal .modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}

#assessmentInfoModal .modal-header .close {
    color: #fff;
    font-size: 1.6rem;
    opacity: 0.9;
    text-shadow: none;
}

#assessmentInfoModal .modal-header .close:hover {
    opacity: 1;
    color: #f8f9fa;
}

/* Body */
#assessmentInfoModal .modal-body {
    padding: 20px 26px;
    max-height: calc(100vh - 240px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

/* Main content */
#assessmentInfoContent {
    color: #2d3748;
    font-size: 1.05rem;
    line-height: 1.7;
}

/* Cards */
.assess-card {
    background: #fdfdff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 16px;
    box-shadow: 0 4px 14px rgba(66, 82, 126, 0.08);
}

.assess-card strong {
    display: block;
    color: #34495e;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 6px;
}

.assess-card ul {
    margin: 8px 0 0 22px;
    padding: 0;
    color: #374151;
    font-size: 1.2rem;
}

.assess-card li {
    margin-bottom: 6px;
}

/* Note section */
.assess-note {
    background: linear-gradient(180deg, #fffef6, #fff8e6);
    border: 1px solid #fff0c2;
    padding: 14px 16px;
    border-radius: 10px;
    color: #6b4e00;
    font-size: 1.05rem;
    line-height: 1.6;
}

/* Divider under dynamic content */
#assessmentInfoModal .modal-body hr {
    border: none;
    border-top: 1px solid #eee;
    margin: 14px 0;
}

/* Scrollbar styling */
#assessmentInfoModal .modal-body::-webkit-scrollbar {
    width: 8px;
}

#assessmentInfoModal .modal-body::-webkit-scrollbar-thumb {
    background: #c3c7d1;
    border-radius: 8px;
}

#assessmentInfoModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a5a9b6;
}

/* Info button style */
.assessment-info .info-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: #2b2b2b;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
}

.assessment-info .info-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

/* Responsive tweaks */
@media (max-width: 600px) {
    #assessmentInfoModal .modal-dialog {
        margin: 14px;
        max-width: calc(100% - 28px);
    }

    #assessmentInfoModal .modal-body {
        padding: 16px;
        font-size: 1rem;
    }
}
</style>




































































































<div class="timetable-wrapper">



    <!-- TIMETABLE GRID -->
    <div class="timetable-grid" id="timetableGrid">
        <!-- header + rows injected here -->

        <!-- HEADER ROW (STICKY) -->
        <div class="tt-row tt-head">
            <div class="tt-cell day-time-head">
                <span class="day-label">Day</span>
                <span class="time-label">Time</span>
            </div>
            <!-- time slots injected dynamically -->
        </div>

        <!-- BODY ROWS (SCROLL) -->
        <!-- days injected dynamically -->
    </div>


    <!-- FOOTER -->
    <div class="timetable-footer">
        <button class="btn btn-warning btn-lg px-5" id="editTimetableBtn">
            Edit
        </button>

        <div id="timetableEditActions" class="hidden">
            <button class="btn btn-default btn-lg mr-2" id="cancelTimetableEdit">
                Cancel
            </button>
            <button class="btn btn-success btn-lg" id="saveTimetableEdit">
                Save
            </button>
        </div>
    </div>

</div>



<div class="modal fade" id="subjectSelectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content subject-modal">

            <!-- HEADER -->
            <div class="modal-header subject-modal-header">
                <h4 class="modal-title">Subjects</h4>

                <div class="subject-search-wrap">
                    <input type="text"
                        class="form-control input-sm"
                        placeholder="Search by name or ID"
                        id="subjectSearch">
                    <i class="fa fa-search"></i>
                </div>

                <button type="button"
                    class="close"
                    data-dismiss="modal">&times;</button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <div class="subject-grid">
                    <!-- subject-item injected here -->
                </div>
            </div>

        </div>
    </div>
</div>



<script>
    /* =====================================================
   GLOBAL STATE
===================================================== */
    window.TIMETABLE_EDIT_MODE = window.TIMETABLE_EDIT_MODE ?? false;
    window.CURRENT_CELL = window.CURRENT_CELL ?? null;





    /* =====================================================
       MODE HELPERS
    ===================================================== */
    function enterEditMode() {
        window.TIMETABLE_EDIT_MODE = true;

        $('#editTimetableBtn').addClass('hidden');
        $('#timetableEditActions').removeClass('hidden');

        $('.timetable-wrapper').addClass('edit-mode');
    }

    function enterViewMode() {
        window.TIMETABLE_EDIT_MODE = false;

        $('#timetableEditActions').addClass('hidden');
        $('#editTimetableBtn').removeClass('hidden');

        $('.timetable-wrapper').removeClass('edit-mode');
    }

    $(document).ready(function() {
        enterViewMode(); // 🔥 force clean start every load
    });



    /* =====================================================
       BUTTON EVENTS
    ===================================================== */
    $(document).on('click', '#editTimetableBtn', function() {
        enterEditMode();
    });

    $(document).on('click', '#cancelTimetableEdit', function() {
        enterViewMode();

        if (typeof loadTimetable === 'function') {
            loadTimetable();
        }
    });

    $(document).on('click', '#saveTimetableEdit', function() {
        enterViewMode();
        alert('Timetable saved');
    });

    /* =====================================================
       SUBJECT CELL CLICK
    ===================================================== */
    $(document).on('click', '.tt-cell.subject', function() {
        if (!window.TIMETABLE_EDIT_MODE) return;

        window.CURRENT_CELL = $(this);
        $('#subjectSelectModal').modal('show');
    });

    /* =====================================================
       SUBJECT SELECT
    ===================================================== */
    $(document).on('click', '.subject-item', function() {
        if (window.CURRENT_CELL) {
            window.CURRENT_CELL.text($(this).text().trim());
        }

        $('#subjectSelectModal').modal('hide');
    });


    function ampmToMinutes(timeStr) {
        const [time, mod] = timeStr.trim().split(/(AM|PM)/);
        let [h, m] = time.split(':').map(Number);

        if (mod === 'PM' && h !== 12) h += 12;
        if (mod === 'AM' && h === 12) h = 0;

        return h * 60 + m;
    }

    function minutesToAMPM(mins) {
        let h = Math.floor(mins / 60);
        let m = mins % 60;
        const mod = h >= 12 ? 'PM' : 'AM';

        h = h % 12 || 12;
        return `${h}:${String(m).padStart(2, '0')}${mod}`;
    }

    function fetchTimetableSettingsAndBuild() {
        $.getJSON(BASE_URL + 'classes/get_timetable_settings', function(res) {
            if (!res || !res.Start_time || !res.End_time) return;
            buildTimetable(res);
        });
    }



    function buildTimetable(settings) {

        const start = ampmToMinutes(settings.Start_time);
        const end = ampmToMinutes(settings.End_time);
        const len = parseInt(settings.Length_of_period, 10);

        const recesses = (settings.Recess_breaks || []).map(r => {

            // 🔥 HANDLE ARRAY OR STRING
            if (Array.isArray(r)) {
                r = r[0]; // unwrap ["01:00PM - 02:00PM"]
            }

            if (typeof r !== 'string') return null;

            const parts = r.split(' - ');
            if (parts.length !== 2) return null;

            const [from, to] = parts;

            return {
                from: ampmToMinutes(from.trim()),
                to: ampmToMinutes(to.trim())
            };
        }).filter(Boolean); // remove invalid entries


        let slots = [];
        let cur = start;

        while (cur < end) {

            const recess = recesses.find(r => cur >= r.from && cur < r.to);

            if (recess) {
                const blocks = Math.ceil((recess.to - cur) / len);
                for (let i = 0; i < blocks; i++) {
                    slots.push({
                        type: 'break'
                    });
                }
                cur = recess.to;
                continue;
            }

            const next = Math.min(cur + len, end);
            slots.push({
                type: 'period',
                from: minutesToAMPM(cur),
                to: minutesToAMPM(next)
            });

            cur = next;
        }

        renderTimetable(slots);
    }



    function renderTimetable(slots) {

        const $grid = $('#timetableGrid');
        $grid.empty();

        /* HEADER */
        let header = `
        <div class="tt-row tt-head">
            <div class="tt-cell day-time-head">
                <span class="day-label">Day</span>
                <span class="time-label">Time</span>
            </div>
    `;

        slots.forEach(s => {
            header += s.type === 'break' ?
                `<div class="tt-cell time-head break-head">LUNCH BREAK</div>` :
                `<div class="tt-cell time-head">${s.from} – ${s.to}</div>`;
        });

        header += `</div>`;
        $grid.append(header);

        /* DAYS */
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        days.forEach(day => {
            let row = `<div class="tt-row"><div class="tt-cell day">${day}</div>`;

            slots.forEach(s => {
                row += s.type === 'break' ?
                    `<div class="tt-cell break-cell">BREAK</div>` :
                    `<div class="tt-cell subject">Select subject</div>`;
            });

            row += `</div>`;
            $grid.append(row);
        });
    }
    /* =====================================================
          INITIAL STATE (CRITICAL)
       ===================================================== */
    $(document).ready(function() {
        enterViewMode(); // force clean start
        fetchTimetableSettingsAndBuild();

    });
</script>


<style>
    /* =====================================================
   TIMETABLE WRAPPER
===================================================== */
    .timetable-wrapper {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
    }

    /* =====================================================
   SCROLL CONTAINER
===================================================== */
    .timetable-scroll {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: auto;
    }

    /* =====================================================
   GRID
===================================================== */
    .timetable-grid {
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-width: max-content;
        /* 🔥 allow horizontal growth */
    }

    /* =====================================================
   ROW (FORCE SINGLE ROW)
===================================================== */
    .tt-row {
        display: grid;
        grid-auto-flow: column;
        /* 🔥 no wrapping */
        grid-auto-columns: 180px;
        /* fixed slot width */
        gap: 16px;
        align-items: stretch;
    }

    /* =====================================================
   CELL BASE
===================================================== */
    .tt-cell {
        height: 44px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* =====================================================
   HEADER (TIME)
===================================================== */
    .tt-head {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #ffffff;
    }

    .tt-head .tt-cell:not(.day-time-head) {
        background: #e9ecef;
        color: #666;
        font-weight: 600;
        min-width: 180px;
        text-align: center;
    }

    /* =====================================================
   DAY | TIME SPLIT CELL
===================================================== */
    .day-time-head {
        position: sticky;
        left: 0;
        z-index: 11;

        height: 44px;
        min-height: 44px;

        display: flex;
        /* 🔥 FIX */
        padding: 0;
        border-radius: 6px;
        overflow: hidden;
        font-weight: 600;
    }

    .day-time-head span {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-time-head .day-label,
    .day-time-head .time-label {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .day-time-head .day-label {
        background: #7f9fa9;
        color: #ffffff;
    }

    .day-time-head .time-label {
        background: #e9ecef;
        color: #666666;
    }

    /* =====================================================
   DAY COLUMN
===================================================== */
    .day {
        position: sticky;
        left: 0;
        z-index: 9;
        background: #7f9fa9;
        color: #ffffff;
        font-weight: 600;
    }

    /* =====================================================
   SUBJECT CELLS
===================================================== */
    .subject {
        background: #7f8f96;
        color: #ffffff;
        transition: background 0.2s ease;
    }

    /* =====================================================
   EDIT MODE
===================================================== */
    .timetable-wrapper:not(.edit-mode) .subject {
        pointer-events: none;
        opacity: 0.6;
        cursor: default;
    }

    .timetable-wrapper.edit-mode .subject {
        cursor: pointer;
        outline: 2px dashed rgba(255, 193, 7, 0.6);
    }

    .timetable-wrapper.edit-mode .subject:hover {
        background: #6f8289;
    }

    /* =====================================================
   BREAK COLUMN
===================================================== */
    .break-head {
        background: #f4c430 !important;
        color: #333;
        font-weight: 700;
    }

    .break-cell {
        background: #fff3cd;
        font-weight: 700;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        letter-spacing: 1px;
    }

    /* =====================================================
   FOOTER
===================================================== */
    .timetable-footer {
        margin-top: 36px;
        text-align: center;
    }

    /* =====================================================
   SUBJECT MODAL
===================================================== */
    .subject-modal {
        border-radius: 20px;
        padding: 10px;
    }

    .subject-modal-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: none;
    }

    .subject-search-wrap {
        position: relative;
        width: 240px;
    }

    .subject-search-wrap input {
        padding-right: 32px;
        border-radius: 8px;
    }

    .subject-search-wrap i {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #f4a000;
    }

    .subject-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
        padding: 20px;
    }

    .subject-item {
        background: #ededed;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        font-size: 13px;
        cursor: pointer;
        color: #888;
    }

    .subject-item:hover {
        background: #dcdcdc;
        color: #333;
    }
</style>