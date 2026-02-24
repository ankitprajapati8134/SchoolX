<?php
// Pagination variables (defensive)
$totalClasses   = is_array($Classes) ? count($Classes) : 0;
$classesPerPage = isset($classesPerPage) ? (int)$classesPerPage : 12; // default to 12
if ($classesPerPage < 1) $classesPerPage = 12;
$totalPages = $totalClasses > 0 ? (int)ceil($totalClasses / $classesPerPage) : 1;
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
                    <button class="btn btn-add-modern" id="addClass">
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

        <!-- ================= addClassPanel (SPA) ================= -->
        <div id="addClassPanel" class="add-class-panel" aria-hidden="true" style="display:none; margin-top:18px;">
            <div class="panel-inner container">
                <div class="panel-header d-flex align-items-center justify-content-between"
                    style="border-bottom:1px solid #eee; padding-bottom:12px;">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-plus-circle text-success panel-icon" style="font-size:28px;"></i>
                        <div class="panel-title-wrap" style="margin-left:12px;">
                            <h3 class="panel-title" style="margin:0; font-size:20px;">Add New Class</h3>
                            <p class="panel-subtitle mb-0" style="color:#666; font-size:13px;">Create a class and select
                                subjects</p>
                        </div>
                    </div>

                    <div class="panel-actions">
                        <button type="button" id="closeAddPanel" class="btn btn-link" aria-label="Close panel">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="panel-body" style="padding-top:16px;">
                    <!-- Form: keep IDs same so your existing JS works -->
                    <form id="subject_form" method="post" novalidate>
                        <div class="row">
                            <!-- LEFT -->
                            <div class="col-md-7 left-column">
                                <div class="form-row">
                                    <label for="classSelect"><i class="fa fa-graduation-cap"></i> Class</label>
                                    <select class="form-control custom-select" id="classSelect" name="class_name"
                                        required>
                                        <option value="" disabled selected>Select Class</option>
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

                                <!-- Subjects injected by renderSubjects -->
                                <div id="subjects_holder" style="margin-top:12px;"></div>
                            </div>

                            <!-- RIGHT -->
                            <div class="col-md-5 right-column">
                                <div class="selected-box" style="border-left:1px solid #f1f1f1; padding-left:16px;">
                                    <div>
                                        <div class="selected-header d-flex justify-content-between align-items-center">
                                            <span><i class="fa fa-check-circle"></i> Selected Subjects</span>
                                            <button type="button" class="clear-btn btn btn-link" id="clearSelected"><i
                                                    class="fa fa-trash"></i> Clear all</button>
                                        </div>
                                        <div id="selected_subjects" class="mt-2"></div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="save-btn btn btn-primary"><i
                                                class="fa fa-save"></i> Save Class</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>



        <div id="classPanelBackdrop" class="class-panel-backdrop" aria-hidden="true"></div>


        <!-- ================= end addClassPanel ================= -->

    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- <script>
    /* Animated panel open/close — fullpage behaviour (no blur/backdrop by default) */
    $(document).ready(function() {
        const $addBtn = $('#addClass');
        const $panel = $('#addClassPanel');
        const $grid = $('.modern-grid-wrapper');
        const $header = $('.page-header-modern');
        const $backdrop = $('#classPanelBackdrop');

        // Make sure backdrop doesn't blur underlying content by default.
        // We'll keep the element for optional use but we won't show the blur.
        $backdrop.removeClass('visible').css({
            'backdrop-filter': 'none'
        });

        function openPanelAnimated() {
            if (!$panel.length) return;
            // save scroll pos so we can restore on close
            window._savedScroll = $(window).scrollTop();

            // Hide header + grid so panel feels like a new page
            $header.hide();
            $grid.hide();

            // Make panel "fullpage": remove rounded corners/shadow and make it flush
            $panel.removeClass('anim-slide-out').addClass('is-visible fullpage').show();
            // Force reflow so animation classes apply properly
            void $panel[0].offsetWidth;
            $panel.addClass('anim-slide-in').attr('aria-hidden', 'false');

            // Do NOT show/backdrop blur by default. If you want a subtle overlay later,
            // you can uncomment the next line.
            // $backdrop.addClass('visible');

            // push history state (so back button closes)
            history.pushState({
                panel: 'add'
            }, '', '?action=add');

            // reset selection UI (safe)
            $('#selected_subjects').empty();
            $('.subject-btn.active').removeClass('active').attr('aria-pressed', 'false');
            $('.language-dropdown, .subject-dropdown').val('');
            $('.subject-btn').filter(function() {
                return !!$(this).attr('data-dropdown-lang');
            }).remove();

            // ensure panel scrollTop=0
            $panel.scrollTop(0);
        }

        function closePanelAnimated() {
            if (!$panel.length) {
                // fallback restore
                $grid.show();
                $header.show();
                if (typeof window._savedScroll !== 'undefined') $(window).scrollTop(window._savedScroll);
                return;
            }

            // animate out
            $panel.removeClass('anim-slide-in');
            $panel.addClass('anim-slide-out').attr('aria-hidden', 'true');

            // hide backdrop if it was visible (we didn't show it by default)
            $backdrop.removeClass('visible');

            // on animation end fully hide and restore header/grid
            $panel.off('animationend.closePanel').on('animationend.closePanel', function() {
                $panel.hide().removeClass('is-visible anim-slide-in anim-slide-out fullpage');
                $grid.show();
                $header.show();
                if (typeof window._savedScroll !== 'undefined') $(window).scrollTop(window._savedScroll);
                $panel.off('animationend.closePanel');
            });
        }

        // Add click handler for Add button
        $addBtn.off('click').on('click', function(e) {
            e.preventDefault();
            if ($panel.length) {
                openPanelAnimated();
            } else {
                window.location.href = 'add_class.php';
            }
        });

        // close handlers
        $('#closeAddPanel, #cancelAdd').off('click').on('click', function() {
            if ($panel.length) {
                if (history.state && history.state.panel === 'add') history.back();
                else closePanelAnimated();
            } else {
                if (typeof window._savedScroll !== 'undefined') $(window).scrollTop(window._savedScroll);
            }
        });

        // popstate handling: animate in/out to reflect history changes
        window.addEventListener('popstate', function(ev) {
            if (!$panel.length) return;
            if (ev.state && ev.state.panel === 'add') {
                // open without double-pushing history
                window._savedScroll = window._savedScroll || $(window).scrollTop();
                openPanelAnimated();
            } else {
                closePanelAnimated();
            }
        });

        // auto-open if URL contains ?action=add (only if panel exists)
        if (location.search.indexOf('action=add') !== -1 && $panel.length) {
            // open directly (no extra history push)
            openPanelAnimated();
        }
    });
</script> -->


<script>
    /* Panel open/close — improved fullpage behaviour + robust reset */
    $(document).ready(function() {
        const $addBtn = $('#addClass');
        const $panel = $('#addClassPanel');
        const $grid = $('.modern-grid-wrapper');
        const $header = $('.page-header-modern');
        const $backdrop = $('#classPanelBackdrop');

        // Keep backdrop element, but don't blur underlying content by default
        $backdrop.removeClass('visible').css({
            'backdrop-filter': 'none'
        });

        // --------------------------
        // Helper: completely clear subject UI when no class selected
        // --------------------------
        function clearSubjectsDisplay() {
            const $holder = $('#subjects_holder');
            if ($holder.length) {
                $holder.empty();
            }

            // clear selected subject list
            $('#selected_subjects').empty();

            // remove any active states on subject buttons/cards
            $('.subject-btn, .subject-card, .subject-section, .stream-card').removeClass('active category-invalid group-warning').attr('aria-pressed', 'false');

            // remove any detailed panels (defensive)
            $('.subject-details, .subject-expanded').remove();

            // unbind events attached to the holder to avoid duplicate handlers later
            $holder.off();
        }

        // --------------------------
        // Global: reset panel UI state
        // --------------------------
        // Exposed so other scripts (and tests) can call it.
        window.resetAddClassPanelState = function(opts = {}) {
            // 1. clear selected list
            $('#selected_subjects').empty();

            // 2. remove dropdown-created buttons
            $('.subject-btn').filter(function() {
                return !!$(this).attr('data-dropdown-lang');
            }).remove();

            // 3. reset active state on remaining subject buttons
            $('.subject-btn').removeClass('active').attr('aria-pressed', 'false');

            // 4. reset selects (language/subject) and trigger change handlers (if any)
            $('.language-dropdown, .subject-dropdown').each(function() {
                $(this).val('');
                $(this).trigger('change');
            });

            // 5. clear validation highlights
            $('.subject-section, .subject-card, .stream-card').removeClass('category-invalid group-warning');

            // 6. optionally reset class selection to default (by default we reset class select).
            if (!opts.preserveClass) {
                const $classSelect = $('#classSelect');
                if ($classSelect.length) {
                    // select the placeholder option (index 0) and trigger change so handlers run
                    $classSelect.prop('selectedIndex', 0).trigger('change');
                }
                // ensure any previously rendered subject UI is fully cleared
                clearSubjectsDisplay();
            }

            // 7. scroll panel to top
            if ($panel && $panel.length) $panel.scrollTop(0);
        };

        // --------------------------
        // Opening: animate + clean state
        // --------------------------
        function openPanelAnimated() {
            if (!$panel.length) return;

            // store scroll pos to restore later
            window._savedScroll = $(window).scrollTop();

            // hide header + grid (fullpage experience)
            $header.hide();
            $grid.hide();

            // ensure a clean UI before showing (prevents stale UI)
            window.resetAddClassPanelState();

            // show/animate panel as fullpage
            $panel.removeClass('anim-slide-out').addClass('is-visible fullpage').show();
            void $panel[0].offsetWidth; // force reflow
            $panel.addClass('anim-slide-in').attr('aria-hidden', 'false');

            // focus the first interactive control for accessibility
            $panel.find('select, input, button, a').filter(':visible').first().focus();

            // optional backdrop: keep hidden by default. Uncomment to enable:
            // $backdrop.addClass('visible');

            // push history entry so browser back closes panel
            history.pushState({
                panel: 'add'
            }, '', '?action=add');
        }

        // --------------------------
        // Closing: animate out and restore
        // --------------------------
        function closePanelAnimated() {
            if (!$panel.length) {
                // fallback restore
                $grid.show();
                $header.show();
                if (typeof window._savedScroll !== 'undefined') $(window).scrollTop(window._savedScroll);
                return;
            }

            // animate out
            $panel.removeClass('anim-slide-in');
            $panel.addClass('anim-slide-out').attr('aria-hidden', 'true');

            // hide backdrop if/when used
            $backdrop.removeClass('visible');

            // after animation finishes, fully hide and restore header/grid
            $panel.off('animationend.closePanel').on('animationend.closePanel', function() {
                $panel.hide().removeClass('is-visible anim-slide-in anim-slide-out fullpage');

                // restore page layout
                $grid.show();
                $header.show();

                // restore scroll
                if (typeof window._savedScroll !== 'undefined') $(window).scrollTop(window._savedScroll);

                // Defensive: ensure subjects cleared (avoid stale DOM)
                clearSubjectsDisplay();

                // Ensure UI is fully reset after hide to avoid stale JS state (no visible flicker).
                window.resetAddClassPanelState();

                $panel.off('animationend.closePanel');
            });
        }

        // Add button: open panel (or fallback redirect)
        $addBtn.off('click').on('click', function(e) {
            e.preventDefault();
            if ($panel.length) openPanelAnimated();
            else window.location.href = 'add_class.php';
        });

        // Close handlers: cross & cancel
        $('#closeAddPanel, #cancelAdd').off('click').on('click', function() {
            if ($panel.length) {
                // prefer using history if pushed, so browser back behaviour stays consistent
                if (history.state && history.state.panel === 'add') history.back();
                else closePanelAnimated();
            } else {
                if (typeof window._savedScroll !== 'undefined') $(window).scrollTop(window._savedScroll);
            }
        });

        // --------------------------
        // Class dropdown handler
        // --------------------------
        $('#classSelect').off('change').on('change', function() {
            const val = $(this).val();

            // If placeholder or empty value selected -> clear the subject UI
            if (!val) {
                clearSubjectsDisplay();
                return; // do not call renderSubjects
            }

            // For real class selection, first fully clear any existing UI then render new subjects
            clearSubjectsDisplay();

            // Call renderSubjects if defined (defensive)
            if (typeof renderSubjects === 'function') {
                try {
                    renderSubjects(val);
                } catch (err) {
                    console.error('renderSubjects threw an error:', err);
                }
            } else {
                console.warn('renderSubjects not defined; implement renderSubjects(className) to populate subjects.');
            }
        });

        // popstate: sync animation with history navigation
        window.addEventListener('popstate', function(ev) {
            if (!$panel.length) return;
            if (ev.state && ev.state.panel === 'add') {
                // ensure we have saved scroll
                window._savedScroll = window._savedScroll || $(window).scrollTop();
                openPanelAnimated();
            } else {
                closePanelAnimated();
            }
        });

        // auto-open when ?action=add present
        if (location.search.indexOf('action=add') !== -1 && $panel.length) {
            openPanelAnimated();
        }
    });
</script>







<script>
    $(document).ready(function() {

        // let languageRules = {}; // Fetched dynamically
        window.languageRules = window.languageRules || {};
        window.languageRules.min = parseInt(window.languageRules.min || 1, 10) || 1;
        window.languageRules.max = parseInt(window.languageRules.min || 2, 10) || 2;


        // When class is selected
        // $('#classSelect').on('change', function() {
        //     const selectedClass = $(this).val();
        //     if (selectedClass === "Select Class") return;

        //     $.ajax({
        //         url: "<?= base_url('classes/fetch_subjects') ?>",
        //         method: 'POST',
        //         data: {
        //             class: selectedClass
        //         },
        //         dataType: 'json',
        //         success: function(response) {
        //             renderSubjects(response);
        //         },
        //         error: function(xhr, status, error) {
        //             console.error("AJAX Error:", status, error);
        //         }
        //     });
        // });

        $('#classSelect').off('change').on('change', function() {
            const selectedClass = $(this).val();
            if (!selectedClass || selectedClass === "Select Class") return;

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

        // function renderSubjects(subjectsData) {
        //     const leftColumn = $('.left-column');

        //     // 🧹 Clear old UI
        //     leftColumn.find('.subject-section').remove();
        //     $('#selected_subjects').empty();
        //     $('.subject-btn.active').removeClass('active').attr('aria-pressed', 'false');
        //     $('.language-dropdown, .subject-dropdown').val('');
        //     // remove dropdown-created buttons
        //     $('.subject-btn').filter(function() {
        //         return !!$(this).attr('data-dropdown-lang');
        //     }).remove();

        //     // inject validation CSS (only once)
        //     if ($('#renderSubjectsValidationStyles').length === 0) {
        //         $('head').append(`
        //     <style id="renderSubjectsValidationStyles">
        //         .category-invalid { border: 2px solid #dc3545 !important; border-radius: 6px; padding: 8px; }
        //     </style>
        // `);
        //     }

        //     // 🧾 Reset language rule defaults (safe parse)
        //     if (typeof languageRules === 'undefined' || languageRules === null) window.languageRules = {};
        //     languageRules = languageRules || {};
        //     languageRules.min = parseInt(languageRules.min || 1, 10) || 1;
        //     languageRules.max = parseInt(languageRules.max || 2, 10) || 2;

        //     // escapeHtml helper
        //     function escapeHtml(unsafe) {
        //         if (unsafe === null || unsafe === undefined) return '';
        //         return String(unsafe)
        //             .replace(/&/g, "&amp;")
        //             .replace(/</g, "&lt;")
        //             .replace(/>/g, "&gt;")
        //             .replace(/"/g, "&quot;")
        //             .replace(/'/g, "&#039;");
        //     }

        //     // 📘 Ensure assessment modal exists only once
        //     if ($('#assessmentInfoModal').length === 0) {
        //         $('body').append(`
        //     <div class="modal fade" id="assessmentInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
        //         <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        //         <div class="modal-content">
        //             <div class="modal-header">
        //             <h5 class="modal-title"><span style="display:inline-block;margin-right:8px">📘</span><span>Assessment Information &amp; Rules</span></h5>
        //             <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        //                 <span aria-hidden="true">&times;</span>
        //             </button>
        //             </div>

        //             <div class="modal-body">
        //             <div id="assessmentInfoContent">
        //                 <!-- populated dynamically -->
        //             </div>

        //             <div style="margin-top:14px">
        //                 <hr style="border:none;border-top:1px solid #eef1f5;margin:12px 0;">
        //                 <div class="assess-note">
        //                 <strong>Note:</strong>
        //                 <div style="margin-top:6px">The pattern is based on CBSE guidelines for all classes. Please consult your school-specific rules for any local variations.</div>
        //                 </div>
        //             </div>
        //             </div>
        //         </div>
        //         </div>
        //     </div>
        // `);
        //     }



        function renderSubjects(subjectsData) {
            const leftColumn = $('.left-column');

            // ---------- Clear old UI (important to prevent stale state) ----------
            leftColumn.find('.subject-section').remove();
            $('#selected_subjects').empty();
            $('.subject-btn').removeClass('active').attr('aria-pressed', 'false');
            $('.language-dropdown, .subject-dropdown').val('');
            $('.subject-btn').filter(function() {
                return !!$(this).attr('data-dropdown-lang');
            }).remove();

            // inject validation CSS once
            if ($('#renderSubjectsValidationStyles').length === 0) {
                $('head').append(`
                <style id="renderSubjectsValidationStyles">
                    .category-invalid { border: 2px solid #dc3545 !important; border-radius: 6px; padding: 8px; }
                    .group-warning { outline: 2px solid #ffc107; }
                </style>
            `);
            }

            // ensure languageRules defaults
            window.languageRules = window.languageRules || {};
            window.languageRules.min = parseInt(window.languageRules.min || 1, 10) || 1;
            window.languageRules.max = parseInt(window.languageRules.max || 2, 10) || 2;

            // small helper
            function escapeHtml(unsafe) {
                if (unsafe === null || unsafe === undefined) return '';
                return String(unsafe)
                    .replace(/&/g, "&amp;").replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // ensure assessment modal exists once
            if ($('#assessmentInfoModal').length === 0) {
                $('body').append(`
                <div class="modal fade" id="assessmentInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><span style="display:inline-block;margin-right:8px">📘</span><span>Assessment Information &amp; Rules</span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div id="assessmentInfoContent"></div>
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

            // 🔧 Helper: remove selected subject by name (safer selectors)
            // function removeSelectedSubjectByName(name, group, dropdownLang = null) {
            //     const sel = $('#selected_subjects .selected-subject').filter(function() {
            //         return String($(this).data('group')) === String(group) && String($(this).data(
            //             'subject-name')) === String(name);
            //     }).first();

            //     if (sel.length) {
            //         const isDropdown = !!sel.data('is-dropdown-created');
            //         const dropdownLangName = sel.data('dropdown-lang');
            //         sel.remove();

            //         if (isDropdown && dropdownLangName) {
            //             // remove matching subject-btn(s) by filtering data attrs
            //             $('.subject-btn').filter(function() {
            //                 return String($(this).data('dropdown-lang')) === String(dropdownLangName) &&
            //                     String($(this).data('group')) === String(group) &&
            //                     String($(this).data('subject-name')) === String(name);
            //             }).remove();
            //         } else {
            //             $('.subject-btn').filter(function() {
            //                 return String($(this).data('group')) === String(group) && String($(this).data(
            //                     'subject-name')) === String(name);
            //             }).removeClass('active').attr('aria-pressed', 'false');
            //         }

            //         // after removal validate category
            //         try {
            //             const section = $(`.subject-section[data-group="${group}"]`).first();
            //             validateCategorySelection(group, section, null);
            //         } catch (err) {
            //             /* ignore */
            //         }
            //     }
            // }

            function removeSelectedSubjectByName(name, group, dropdownLang = null) {
                const sel = $('#selected_subjects .selected-subject').filter(function() {
                    return String($(this).data('group')) === String(group) && String($(this).data(
                        'subject-name')) === String(name);
                }).first();

                if (!sel.length) return;

                const isDropdown = !!sel.data('is-dropdown-created');
                const dropdownLangName = sel.data('dropdown-lang');
                sel.remove();

                if (isDropdown && dropdownLangName) {
                    $('.subject-btn').filter(function() {
                        return String($(this).data('dropdown-lang')) === String(dropdownLangName) &&
                            String($(this).data('group')) === String(group) &&
                            String($(this).data('subject-name')) === String(name);
                    }).remove();
                } else {
                    $('.subject-btn').filter(function() {
                        return String($(this).data('group')) === String(group) && String($(this).data(
                            'subject-name')) === String(name);
                    }).removeClass('active').attr('aria-pressed', 'false');
                }

                try {
                    const section = $(`.subject-section[data-group="${group}"]`).first();
                    validateCategorySelection(group, section, null);
                } catch (err) {
                    /* ignore */
                }
            }



            // 🔧 Helper: check duplicate subjects (robust)
            // function isSubjectAlreadySelected(name, group) {
            //     const inSelectedList = $('#selected_subjects .selected-subject').filter(function() {
            //         return String($(this).data('group')) === String(group) && String($(this).data(
            //             'subject-name')) === String(name);
            //     }).length > 0;

            //     const activeBtn = $('.subject-btn').filter(function() {
            //         return String($(this).data('group')) === String(group) && String($(this).data(
            //             'subject-name')) === String(name) && $(this).hasClass('active');
            //     }).length > 0;

            //     return inSelectedList || activeBtn;
            // }

            function isSubjectAlreadySelected(name, group) {
                const inSelectedList = $('#selected_subjects .selected-subject').filter(function() {
                    return String($(this).data('group')) === String(group) && String($(this).data(
                        'subject-name')) === String(name);
                }).length > 0;

                const activeBtn = $('.subject-btn').filter(function() {
                    return String($(this).data('group')) === String(group) && String($(this).data(
                        'subject-name')) === String(name) && $(this).hasClass('active');
                }).length > 0;

                return inSelectedList || activeBtn;
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
                if (/^[+-]?\d+(\.\d+)?$/.test(str)) return false;
                return true;
            }

            // Helper: build info text according to the rules you specified
            function buildInfoText(isCompulsory, rulesObj) {
                const minVal = (rulesObj && (rulesObj.min_select !== undefined ? rulesObj.min_select : (rulesObj
                    .min !== undefined ? rulesObj.min : null)));
                const maxVal = (rulesObj && (rulesObj.max_select !== undefined ? rulesObj.max_select : (rulesObj
                    .max !== undefined ? rulesObj.max : null)));

                if (minVal !== null || maxVal !== null) {
                    if (minVal !== null && maxVal !== null) {
                        return `Select minimum ${minVal} and maximum ${maxVal} subjects.`;
                    } else if (minVal !== null) {
                        return `Select minimum ${minVal} subjects.`;
                    } else {
                        return `Select maximum ${maxVal} subjects.`;
                    }
                }

                if (isCompulsory) {
                    return 'Compulsory as per syllabus';
                } else {
                    return 'Optional subjects - select as per school availability';
                }
            }

            // Helper: extract numeric min/max from a rules object (returns {min: number|null, max: number|null})
            function extractMinMax(rulesObj) {
                if (!rulesObj || typeof rulesObj !== 'object') return {
                    min: null,
                    max: null
                };
                const min = (rulesObj.min_select !== undefined ? Number(rulesObj.min_select) : (rulesObj.min !==
                    undefined ? Number(rulesObj.min) : null));
                const max = (rulesObj.max_select !== undefined ? Number(rulesObj.max_select) : (rulesObj.max !==
                    undefined ? Number(rulesObj.max) : null));
                return {
                    min: Number.isFinite(min) ? min : null,
                    max: Number.isFinite(max) ? max : null
                };
            }

            // Helper: Validate category selection counts and add/remove red border for the provided section element
            // groupKey is the data-group string used on buttons (e.g. 'Languages' or 'Streams:Science')
            // sectionElement is a jQuery element corresponding to the UI block to outline (e.g. sectionDiv or streamCard)
            // rulesObj can be provided to extract min/max if available; if null, function will try to find rules on DOM or skip
            function validateCategorySelection(groupKey, sectionElement, rulesObj) {
                try {
                    if (!sectionElement || !sectionElement.length) return;

                    // extract min/max (prefer rulesObj if provided)
                    let mm = extractMinMax(rulesObj);
                    // fallback: try to read from sectionElement data attributes (if you set them elsewhere)
                    if (mm.min === null && mm.max === null) {
                        const rs = sectionElement.data('rules') || null;
                        mm = extractMinMax(rs);
                    }

                    // if no min & no max defined, do not highlight (requirement: only warn when min/max exist)
                    if (mm.min === null && mm.max === null) {
                        sectionElement.removeClass('category-invalid');
                        return;
                    }

                    // count number of active selections for this groupKey
                    // Count both .subject-btn.active and items present in #selected_subjects (unique by subject-name)
                    const namesSet = new Set();

                    // buttons
                    $('.subject-btn').filter(function() {
                        return String($(this).data('group')) === String(groupKey) && $(this).hasClass(
                            'active');
                    }).each(function() {
                        const nm = String($(this).data('subject-name') || $(this).text()).trim();
                        if (nm) namesSet.add(nm);
                    });

                    // selected list entries
                    $('#selected_subjects .selected-subject').filter(function() {
                        return String($(this).data('group')) === String(groupKey);
                    }).each(function() {
                        const nm = String($(this).data('subject-name') || $(this).text()).trim();
                        if (nm) namesSet.add(nm);
                    });

                    const count = namesSet.size;

                    // if count < min OR count > max then invalid
                    let invalid = false;
                    if (mm.min !== null && count < mm.min) invalid = true;
                    if (mm.max !== null && count > mm.max) invalid = true;

                    if (invalid) sectionElement.addClass('category-invalid');
                    else sectionElement.removeClass('category-invalid');
                } catch (err) {
                    // fail silently but log
                    console.error('validateCategorySelection error', err);
                }
            }

            // 🧩 Render subjects from fetched data
            $.each(subjectsData, function(group, data) {

                // 🟦 Handle Assessment section dynamically
                if (group === "Assessment") {
                    function makeAssessmentClickHandler(data, subjectsData) {
                        return function(e) {
                            if (e && typeof e.preventDefault === 'function') {
                                e.preventDefault();
                                e.stopPropagation();
                            }

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

                            // rules parsing (keeps original logic)
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

                            let rulesSource = null;
                            if (subjectsData && subjectsData.Core && subjectsData.Core.rules !==
                                undefined) {
                                rulesSource = subjectsData.Core.rules;
                            } else if (subjectsData && subjectsData.rules !== undefined) {
                                rulesSource = subjectsData.rules;
                            }

                            const general = [];
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

                            if (rulesSource && typeof rulesSource === 'object' && Object.prototype
                                .hasOwnProperty.call(rulesSource, 'rules')) {
                                const nested = rulesSource.rules;
                                if (Array.isArray(nested)) addUnique(general, toStrings(nested));
                                else if (nested && typeof nested === 'object') rulesSource = Object
                                    .assign({}, rulesSource, nested);
                                else addUnique(general, toStrings(nested));
                            }

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

                            if (subjectsData && typeof subjectsData === 'object') {
                                Object.entries(subjectsData).forEach(([topKey, topVal]) => {
                                    if (topKey === 'Core' || topKey === 'rules') return;
                                    if (topVal && typeof topVal === 'object' && topVal.rules !==
                                        undefined) {
                                        addCategory(topKey, toStrings(topVal.rules));
                                    }
                                });
                            }

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

                            const dedupGeneral = [];
                            for (const r of general)
                                if (!dedupGeneral.includes(r)) dedupGeneral.push(r);

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

                            // Note handling
                            let note = null;
                            if (data && data.note) note = data.note;
                            else if (subjectsData && subjectsData.Core && subjectsData.Core.note) note =
                                subjectsData.Core.note;
                            else if (subjectsData && subjectsData.note) note = subjectsData.note;
                            const noteHtml = note ?
                                `<div class="mt-2"><strong>Note:</strong><div>${escapeHtml(String(note))}</div></div>` :
                                '';

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
                    }

                    const existing = leftColumn.find('.assessment-info').first();
                    const clickHandler = makeAssessmentClickHandler(data, subjectsData);

                    if (existing.length) {
                        const btn = existing.find('button.info-btn');
                        if (btn.length) {
                            btn.off('click').on('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                clickHandler(e);
                            });
                        } else {
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

                // === STREAMS HANDLER ===
                if (group === 'Streams' && data && typeof data === 'object') {
                    const sectionDiv = $('<div>').addClass('subject-section mb-3').attr('data-group', group)
                        .data('rules', data);
                    const header = $('<div>').addClass('subject-row').append($('<div>').addClass(
                        'subject-label').html(`<strong>Streams</strong>`));
                    sectionDiv.append(header);

                    const streamsContainer = $('<div>').addClass('streams-container mt-2');

                    Object.entries(data).forEach(([streamName, streamDef]) => {
                        if (!streamDef || typeof streamDef !== 'object') return;

                        const streamCard = $('<div>').addClass(
                            'stream-card mb-3 p-2 border rounded').attr('data-stream',
                            streamName).data('rules', streamDef);
                        streamCard.append($('<div>').addClass('stream-heading mb-2').html(
                            `<strong>${escapeHtml(streamName)}</strong>`));

                        // CORE subjects section
                        const coreListDiv = $('<div>').addClass('stream-core mb-2');
                        coreListDiv.append($('<div>').addClass('small mb-1').html('<em>Core</em>'));
                        const coreBtnsDiv = $('<div>').addClass('d-flex flex-wrap gap-2 mb-1');

                        const coreSubjects = Array.isArray(streamDef.core_subjects) ? streamDef
                            .core_subjects : [];
                        const streamGroupKey = `Streams:${streamName}`;

                        coreSubjects.forEach(sub => {
                            const btn = $('<button>')
                                .attr('type', 'button')
                                .addClass('subject-btn')
                                .text(sub)
                                .data('group', streamGroupKey).attr('data-group',
                                    streamGroupKey)
                                .data('subject-name', sub).attr('data-subject-name', sub)
                                .data('stream', streamName)
                                .attr('aria-pressed', 'false')
                                .on('click', function() {
                                    const b = $(this);
                                    const nm = String($(this).data('subject-name') || $(
                                        this).text());
                                    if (b.hasClass('active')) {
                                        removeSelectedSubjectByName(nm, streamGroupKey);
                                        b.removeClass('active').attr('aria-pressed',
                                            'false');
                                    } else {
                                        if (!isSubjectAlreadySelected(nm,
                                                streamGroupKey)) {
                                            if (typeof selectSubject === 'function')
                                                selectSubject(nm, btn, streamGroupKey);
                                            b.addClass('active').attr('aria-pressed',
                                                'true');
                                        }
                                    }
                                    // validate after toggle
                                    validateCategorySelection(streamGroupKey,
                                        streamCard, streamDef);
                                    // also validate Streams parent (if needed)
                                    validateCategorySelection('Streams', sectionDiv,
                                        data);
                                });

                            if (streamDef.compulsory === true) {
                                btn.addClass('active').attr('aria-pressed', 'true');
                                if (!isSubjectAlreadySelected(sub, streamGroupKey)) {
                                    if (typeof selectSubject === 'function') selectSubject(
                                        sub, btn, streamGroupKey);
                                }
                            }

                            coreBtnsDiv.append(btn);
                        });

                        coreListDiv.append(coreBtnsDiv);
                        streamCard.append(coreListDiv);

                        // OPTIONAL subjects section -> DROPDOWN
                        const optionalListDiv = $('<div>').addClass('stream-optional mb-2');
                        optionalListDiv.append($('<div>').addClass('small mb-1').html(
                            '<em>Optional</em>'));

                        const optionalSubjects = Array.isArray(streamDef.optional_subjects) ?
                            streamDef.optional_subjects : [];

                        let optionalDropdown = null;
                        if (optionalSubjects.length > 0) {
                            optionalDropdown = $('<select>')
                                .addClass(
                                    'form-control custom-select subject-dropdown stream-optional-dropdown mb-2'
                                )
                                .append($('<option>').val('').text('Select Optional Subject'));

                            optionalSubjects.forEach(sub => {
                                optionalDropdown.append($('<option>').val(sub).text(sub));
                            });

                            optionalDropdown.on('change', function() {
                                const selectedSub = $(this).val();
                                if (!selectedSub) return;

                                if (isSubjectAlreadySelected(selectedSub, streamGroupKey)) {
                                    $(this).val('');
                                    return;
                                }

                                const btn = $('<button>')
                                    .attr('type', 'button')
                                    .addClass('subject-btn active')
                                    .text(selectedSub)
                                    .data('group', streamGroupKey).attr('data-group',
                                        streamGroupKey)
                                    .data('subject-name', selectedSub).attr(
                                        'data-subject-name', selectedSub)
                                    .data('is-dropdown-created', true)
                                    .attr('data-dropdown-lang', selectedSub)
                                    .on('click', function() {
                                        const b = $(this);
                                        const nm = String($(this).data(
                                            'subject-name') || $(this).text());
                                        if (b.hasClass('active')) {
                                            removeSelectedSubjectByName(nm,
                                                streamGroupKey, nm);
                                            b.removeClass('active');
                                        } else {
                                            selectSubject(nm, b, streamGroupKey, nm);
                                            b.addClass('active');
                                        }
                                        // validate after toggle
                                        validateCategorySelection(streamGroupKey,
                                            streamCard, streamDef);
                                        validateCategorySelection('Streams', sectionDiv,
                                            data);
                                    });

                                $(this).before(btn);

                                if (typeof selectSubject === 'function') selectSubject(
                                    selectedSub, btn, streamGroupKey, selectedSub);

                                // validate after selecting from dropdown
                                validateCategorySelection(streamGroupKey, streamCard,
                                    streamDef);
                                validateCategorySelection('Streams', sectionDiv, data);

                                $(this).val('');
                            });

                            optionalListDiv.append(optionalDropdown);
                        }

                        // ===== FIX: Force show min/max from DB for streams if present =====
                        // Prefer explicit min/max values from streamDef (min_select/min/max_select/max).
                        const explicit = (streamDef && typeof streamDef === 'object') ? {
                            min: (streamDef.min_select !== undefined ? streamDef.min_select : (
                                streamDef.min !== undefined ? streamDef.min : null)),
                            max: (streamDef.max_select !== undefined ? streamDef.max_select : (
                                streamDef.max !== undefined ? streamDef.max : null))
                        } : {
                            min: null,
                            max: null
                        };

                        let streamInfoTextStr;
                        if (explicit.min !== null || explicit.max !== null) {
                            // build explicit text using DB values (exact phrasing)
                            const minVal = explicit.min !== null ? explicit.min : null;
                            const maxVal = explicit.max !== null ? explicit.max : null;
                            if (minVal !== null && maxVal !== null) {
                                streamInfoTextStr =
                                    `Select minimum ${minVal} and maximum ${maxVal} subjects.`;
                            } else if (minVal !== null) {
                                streamInfoTextStr = `Select minimum ${minVal} subjects.`;
                            } else {
                                streamInfoTextStr = `Select maximum ${maxVal} subjects.`;
                            }
                        } else {
                            // fallback to your original buildInfoText logic (keeps existing behavior)
                            const streamRulesObj = streamDef.rules || {
                                min_select: streamDef.min_select,
                                max_select: streamDef.max_select,
                                min: streamDef.min,
                                max: streamDef.max
                            };
                            streamInfoTextStr = buildInfoText(!!streamDef.compulsory,
                                streamRulesObj);
                        }
                        optionalListDiv.append($('<small>').addClass(
                            'text-muted d-block mt-1 stream-info-text').text(
                            streamInfoTextStr));
                        // ===== end fix =====

                        streamCard.append(optionalListDiv);

                        // append and perform initial validate (in case core preselection affects counts)
                        streamsContainer.append(streamCard);
                        validateCategorySelection(streamGroupKey, streamCard, streamDef);
                    });

                    sectionDiv.append(streamsContainer);
                    leftColumn.append(sectionDiv);

                    // validate Streams as a whole (in case there are parent rules)
                    validateCategorySelection('Streams', sectionDiv, data);

                    return; // done with Streams
                } // end Streams special handling

                // 🟨 Subject group UI setup
                const sectionDiv = $('<div>').addClass('subject-section mb-3').attr('data-group', group);
                const rowDiv = $('<div>').addClass('subject-row');
                const labelDiv = $('<div>').addClass('subject-label').html(
                    `<strong>${escapeHtml(group)}</strong>`);
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

                // store rules on sectionDiv so validateCategorySelection can find it later
                sectionDiv.data('rules', rules || {});

                // 📝 Info text for category (updated per your wording rules)
                const infoTextStr = buildInfoText(isCompulsory, rules);
                const infoText = $('<small>').addClass('text-muted d-block mt-1 category-info-text').text(
                    infoTextStr);

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
                        .data('group', 'Languages').attr('data-group', 'Languages')
                        .data('subject-name', 'English').attr('data-subject-name', 'English')
                        .on('click', function() {
                            const btn = $(this);
                            const name = 'English';
                            if (btn.hasClass('active')) {
                                removeSelectedSubjectByName(name, 'Languages');
                                btn.removeClass('active').attr('aria-pressed', 'false');
                            } else {
                                if (!isSubjectAlreadySelected(name, 'Languages')) {
                                    selectSubject(name, btn, 'Languages');
                                    btn.addClass('active').attr('aria-pressed', 'true');
                                }
                            }
                            if (typeof updateLanguageGroupWarning === 'function')
                                updateLanguageGroupWarning();
                            validateCategorySelection('Languages', sectionDiv, rules);
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
                            .filter((i, el) => String($(el).data('group')) === 'Languages').length;

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
                            .data('group', 'Languages').attr('data-group', 'Languages')
                            .data('subject-name', selectedLang).attr('data-subject-name',
                                selectedLang)
                            .attr('data-dropdown-lang', selectedLang)
                            .on('click', function() {
                                const b = $(this);
                                const nm = String($(this).data('subject-name') || $(this)
                                    .text());
                                if (b.hasClass('active')) {
                                    removeSelectedSubjectByName(nm, 'Languages', nm);
                                    b.removeClass('active').attr('aria-pressed', 'false');
                                } else {
                                    selectSubject(nm, b, 'Languages', nm);
                                    b.addClass('active').attr('aria-pressed', 'true');
                                }
                                validateCategorySelection('Languages', sectionDiv, rules);
                            });

                        $(this).before(langBtn);
                        selectSubject(selectedLang, langBtn, 'Languages', selectedLang);
                        $(this).val('');
                        if (typeof updateLanguageGroupWarning === 'function')
                            updateLanguageGroupWarning();
                        validateCategorySelection('Languages', sectionDiv, rules);
                    });

                    buttonsDiv.append(englishBtn).append(dropdown).append(infoText);

                    // initial validation for Languages
                    validateCategorySelection('Languages', sectionDiv, rules);
                }

                // 🔵 Compulsory groups
                else if (isCompulsory) {
                    subjects.forEach(subject => {
                        const btn = $('<button>')
                            .attr('type', 'button')
                            .addClass('subject-btn active')
                            .text(subject)
                            .data('group', group).attr('data-group', group)
                            .data('subject-name', subject).attr('data-subject-name', subject)
                            .on('click', function() {
                                const b = $(this);
                                const nm = String($(this).data('subject-name') || $(this)
                                    .text());
                                if (b.hasClass('active')) {
                                    removeSelectedSubjectByName(nm, group);
                                    b.removeClass('active').attr('aria-pressed', 'false');
                                } else {
                                    if (!isSubjectAlreadySelected(nm, group)) {
                                        selectSubject(nm, btn, group);
                                        btn.addClass('active').attr('aria-pressed', 'true');
                                    }
                                }
                                validateCategorySelection(group, sectionDiv, rules);
                            });
                        buttonsDiv.append(btn);
                        if (!isSubjectAlreadySelected(subject, group)) selectSubject(subject, btn,
                            group);
                    });
                    buttonsDiv.append(infoText);

                    // initial validation for compulsory group (in case it also defines min/max)
                    validateCategorySelection(group, sectionDiv, rules);
                }

                // 🟣 Optional groups
                else {
                    console.log('renderSubjects: optional group=', group, 'subjects=', subjects);

                    buttonsDiv.find('.subject-dropdown').remove();

                    if (group === 'Additional') {
                        // Always show custom input (even when options empty)
                        let dropdown = null;
                        const cleanedOptions = Array.isArray(subjects) ? subjects.map(s => (s === null ||
                            s === undefined) ? '' : String(s).trim()).filter(s => s !== '' && s
                            .toLowerCase() !== 'false') : [];

                        if (cleanedOptions.length > 0) {
                            dropdown = $('<select>').addClass('form-control custom-select subject-dropdown')
                                .append($('<option>').val('').text('Select Subject'));
                            cleanedOptions.forEach(sub => dropdown.append($('<option>').val(sub).text(
                                sub)));
                        } else {
                            // still keep dropdown null but we log for debug
                            console.log(
                                'Additional has NO options after cleaning — custom input will still be present.'
                            );
                        }

                        const inputGroup = $('<div>').addClass('additional-input-group d-flex mt-2').css(
                            'gap', '6px');
                        const customInput = $('<input>').attr('type', 'text').addClass(
                            'form-control additional-subject-input').attr('placeholder',
                            'Type a custom subject and press Enter or Add');
                        const addBtn = $('<button>').attr('type', 'button').addClass(
                            'btn btn-sm btn-primary add-additional-btn').text('Add');

                        // ALWAYS append input group so XI/XII get it (fix for missing custom input)
                        inputGroup.append(customInput).append(addBtn);

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
                                .data('group', group).attr('data-group', group)
                                .data('subject-name', name).attr('data-subject-name', name)
                                .data('is-dropdown-created', true)
                                .attr('data-dropdown-lang', name)
                                .on('click', function() {
                                    const b = $(this);
                                    const nm = String($(this).data('subject-name') || $(this).text());
                                    if (b.hasClass('active')) {
                                        removeSelectedSubjectByName(nm, group, nm);
                                        b.removeClass('active').attr('aria-pressed', 'false');
                                    } else {
                                        selectSubject(nm, b, group, nm);
                                        b.addClass('active').attr('aria-pressed', 'true');
                                    }
                                    validateCategorySelection(group, sectionDiv, rules);
                                });

                            // Prefer to insert before dropdown if it exists, otherwise append
                            if (dropdown && dropdown.parent().length) dropdown.before(btn);
                            else buttonsDiv.append(btn);

                            selectSubject(name, btn, group, name);

                            if (dropdown) {
                                if (dropdown.find(`option[value="${name}"]`).length === 0) {
                                    dropdown.append($('<option>').val(name).text(name));
                                }
                            }

                            if (!subjects.includes(name)) subjects.push(name);
                            customInput.val('');

                            // validate after adding
                            validateCategorySelection(group, sectionDiv, rules);
                        }

                        addBtn.on('click', function() {
                            const v = customInput.val();
                            createAndSelectSubject(v);
                        });

                        customInput.on('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                createAndSelectSubject($(this).val());
                            }
                        });

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
                                    .data('group', group).attr('data-group', group)
                                    .data('subject-name', selectedSub).attr('data-subject-name',
                                        selectedSub)
                                    .data('is-dropdown-created', true)
                                    .attr('data-dropdown-lang', selectedSub)
                                    .on('click', function() {
                                        const b = $(this);
                                        const nm = String($(this).data('subject-name') || $(
                                            this).text());
                                        if (b.hasClass('active')) {
                                            removeSelectedSubjectByName(nm, group, nm);
                                            b.removeClass('active').attr('aria-pressed',
                                                'false');
                                        } else {
                                            selectSubject(nm, b, group, nm);
                                            b.addClass('active').attr('aria-pressed', 'true');
                                        }
                                        validateCategorySelection(group, sectionDiv, rules);
                                    });

                                $(this).before(btn);
                                selectSubject(selectedSub, btn, group, selectedSub);

                                // validate after selecting
                                validateCategorySelection(group, sectionDiv, rules);

                                $(this).val('');
                            });

                            // append dropdown then input (keeps UI order)
                            buttonsDiv.append(dropdown);
                            buttonsDiv.append(inputGroup);
                        } else {
                            // no dropdown - still append the inputGroup so user can add
                            buttonsDiv.append(inputGroup);
                        }

                        buttonsDiv.append(infoText);

                        // initial validation for Additional
                        validateCategorySelection(group, sectionDiv, rules);
                    } else {
                        const dropdown = $('<select>').addClass(
                            'form-control custom-select subject-dropdown').append($('<option>').val('')
                            .text('Select Subject'));
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
                                .data('group', group).attr('data-group', group)
                                .data('subject-name', selectedSub).attr('data-subject-name',
                                    selectedSub)
                                .data('is-dropdown-created', true)
                                .attr('data-dropdown-lang', selectedSub)
                                .on('click', function() {
                                    const b = $(this);
                                    const nm = String($(this).data('subject-name') || $(this)
                                        .text());
                                    if (b.hasClass('active')) {
                                        removeSelectedSubjectByName(nm, group, nm);
                                        b.removeClass('active').attr('aria-pressed', 'false');
                                    } else {
                                        selectSubject(nm, b, group, nm);
                                        b.addClass('active').attr('aria-pressed', 'true');
                                    }
                                    validateCategorySelection(group, sectionDiv, rules);
                                });

                            $(this).before(btn);
                            selectSubject(selectedSub, btn, group, selectedSub);

                            // validate after selecting
                            validateCategorySelection(group, sectionDiv, rules);

                            $(this).val('');
                        });

                        buttonsDiv.append(dropdown).append(infoText);

                        // initial validation for this optional group
                        validateCategorySelection(group, sectionDiv, rules);
                    }
                }

                // 🧱 Append section
                rowDiv.append(labelDiv).append(buttonsDiv);
                sectionDiv.append(rowDiv);
                leftColumn.append(sectionDiv);
            });
        }



        // Select subject
        // function selectSubject(subject, btn, group, dropdownLangName = null) {
        //     const selectedContainer = $('#selected_subjects');

        //     // Prevent duplicates
        //     if ($(`#selected_subjects .selected-subject span:contains("${subject}")`).length) return;

        //     const div = $('<div>')
        //         .addClass('selected-subject')
        //         .html(`<span>${subject}</span><button type="button" class="remove-btn">&times;</button>`)
        //         .data('group', group)
        //         .data('subject-name', subject)
        //         .data('is-dropdown-created', !!dropdownLangName)
        //         .data('dropdown-lang', dropdownLangName)
        //         .data('btn-element', btn);

        //     // Remove subject manually
        //     div.find('.remove-btn').on('click', function() {
        //         const isDropdownCreated = div.data('is-dropdown-created');
        //         const dropdownLang = div.data('dropdown-lang');
        //         const originalBtn = div.data('btn-element');
        //         const subjectGroup = div.data('group');

        //         div.remove();

        //         // If it was from dropdown, delete its dynamically created button
        //         if (isDropdownCreated && dropdownLang) {
        //             $(`.subject-btn[data-dropdown-lang="${dropdownLang}"]`).remove();
        //         } else {
        //             // For regular buttons (like English), just remove active class
        //             originalBtn.removeClass('active');
        //         }

        //         // ✅ Only update warning if it's a Language subject
        //         if (subjectGroup === 'Languages') {
        //             updateLanguageGroupWarning();
        //         }
        //     });

        //     selectedContainer.append(div);
        //     btn.addClass('active');

        //     // ✅ Don't call updateLanguageGroupWarning here - it's called by the click handler
        // }

        function selectSubject(subject, btn, group, dropdownLangName = null) {
            const selectedContainer = $('#selected_subjects');

            // Prevent duplicates (explicit check by data attributes)
            const already = $('#selected_subjects .selected-subject').filter(function() {
                return String($(this).data('group')) === String(group) && String($(this).data(
                    'subject-name')) === String(subject);
            }).length;
            if (already) return;

            const div = $('<div>').addClass('selected-subject')
                .data('group', group)
                .data('subject-name', subject)
                .data('is-dropdown-created', !!dropdownLangName)
                .data('dropdown-lang', dropdownLangName)
                .data('btn-element', btn);

            const span = $('<span>').text(subject);
            const removeBtn = $('<button type="button">').addClass('remove-btn').html('&times;');

            removeBtn.on('click', function() {
                const isDropdownCreated = div.data('is-dropdown-created');
                const dropdownLang = div.data('dropdown-lang');
                const originalBtn = div.data('btn-element');
                const subjectGroup = div.data('group');

                div.remove();

                if (isDropdownCreated && dropdownLang) {
                    $(`.subject-btn[data-dropdown-lang="${dropdownLang}"]`).remove();
                } else if (originalBtn && originalBtn.removeClass) {
                    originalBtn.removeClass('active').attr('aria-pressed', 'false');
                }

                if (subjectGroup === 'Languages' && typeof window.updateLanguageGroupWarning === 'function')
                    window.updateLanguageGroupWarning();
            });

            div.append(span).append(removeBtn);
            selectedContainer.append(div);

            if (btn && btn.addClass) btn.addClass('active').attr('aria-pressed', 'true');
        }



        // Red box warning for language group
        // function updateLanguageGroupWarning() {
        //     const languagesSelected = $('#selected_subjects .selected-subject')
        //         .filter((i, el) => $(el).data('group') === 'Languages').length;

        //     const languagesGroupDiv = $('.subject-section[data-group="Languages"]');
        //     if (languagesGroupDiv.length) {
        //         if (languagesSelected > 0 && languagesSelected < languageRules.min) {
        //             // ✅ Only show warning if at least 1 language is selected but less than minimum
        //             languagesGroupDiv.addClass('group-warning');
        //         } else {
        //             languagesGroupDiv.removeClass('group-warning');
        //         }
        //     }
        // }
        window.updateLanguageGroupWarning = function() {
            const languagesSelected = $('#selected_subjects .selected-subject').filter((i, el) => $(el).data(
                'group') === 'Languages').length;
            const languagesGroupDiv = $('.subject-section[data-group="Languages"]');
            if (languagesGroupDiv.length) {
                if (languagesSelected > 0 && languagesSelected < window.languageRules.min) languagesGroupDiv
                    .addClass('group-warning');
                else languagesGroupDiv.removeClass('group-warning');
            }
        };



        // Clear all selected subjects — robust & safe
        $('#clearSelected').off('click').on('click', function(e) {
            e.preventDefault();

            // 1) remove selected list entries
            $('#selected_subjects').empty();

            // 2) remove dynamically created dropdown buttons
            $('.subject-btn').filter(function() {
                return !!$(this).attr('data-dropdown-lang');
            }).remove();

            // 3) reset active state on remaining subject buttons
            $('.subject-btn').removeClass('active').attr('aria-pressed', 'false');

            // 4) reset selects and trigger change so handlers run
            $('.language-dropdown, .subject-dropdown').each(function() {
                $(this).val('');
                $(this).trigger('change');
            });

            // 5) remove validation highlights
            $('.subject-section, .subject-card, .stream-card').removeClass(
                'category-invalid group-warning');

            // 6) update language warnings
            if (typeof window.updateLanguageGroupWarning === 'function') window
                .updateLanguageGroupWarning();
        });


        // Form submit validation
        // $('#subject_form').on('submit', function(e) {
        //     const languagesSelected = $('#selected_subjects .selected-subject')
        //         .filter((i, el) => $(el).data('group') === 'Languages').length;

        //     if (languagesSelected < window.languageRules.min) {
        //         e.preventDefault();
        //         alert(`Please select at least ${window.languageRules.min} languages.`);
        //         updateLanguageGroupWarning();
        //     }
        // });

        $('#subject_form').off('submit').on('submit', function(e) {
            const languagesSelected = $('#selected_subjects .selected-subject').filter((i, el) => $(el)
                .data('group') === 'Languages').length;
            if (languagesSelected < window.languageRules.min) {
                e.preventDefault();
                alert(`Please select at least ${window.languageRules.min} languages.`);
                if (typeof window.updateLanguageGroupWarning === 'function') window
                    .updateLanguageGroupWarning();
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
    /* ------------------ Page header & controls ------------------ */
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
        gap: 20px;
        flex-wrap: wrap;
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
        transition: all .3s;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-add-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
    }

    /* ------------------ Grid & Cards ------------------ */
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
        transition: all .3s;
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
    }

    .class-icon-wrapper {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.18);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
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

    .btn-edit {
        color: #ffc107;
    }

    /* ------------------ Pagination ------------------ */
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
        transition: all .3s;
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

    /* ------------------ Subject selection / form ------------------ */
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
    }

    .form-row select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.18);
        outline: none;
    }

    .subject-section {
        border-bottom: 1px solid #e9ecef;
        padding: 15px 10px;
        margin-bottom: 10px;
        border-radius: 8px;
        transition: all .2s;
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
        transition: all .18s;
        color: #495057;
    }

    .subject-btn:hover {
        background: #667eea;
        color: #fff;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.18);
    }

    .subject-btn.active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        border-color: #28a745;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
    }

    /* warning / invalid */
    .group-warning {
        border: 2px solid #dc3545;
        background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
        padding: 15px;
        border-radius: 12px;
        animation: pulse-warning 1.5s infinite;
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

    .category-invalid {
        border: 2px solid #dc3545 !important;
        border-radius: 6px;
        padding: 8px;
    }

    /* Selected subjects — improved layout & stretch */
    .right-column {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* container that holds the box; ensures it stretches to fill the column */
    .selected-box {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 16px;
        min-height: 620px;
        /* comfortable default - you can lower this */
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(222, 230, 238, 0.9);
        box-sizing: border-box;
    }

    /* header area remains fixed height, content area grows */
    .selected-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(222, 230, 238, 0.9);
        margin-bottom: 12px;
    }

    /* title */
    .selected-header span {
        font-weight: 700;
        font-size: 16px;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Clear all button — styled with accent color and compact */
    .clear-btn {
        background: var(--accent, #ffc107);
        color: #1a1a1a;
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        cursor: pointer;
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        transition: transform .12s ease, box-shadow .12s ease;
    }

    .clear-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.10);
    }

    /* selected list: grows to fill available vertical space and scrolls */
    #selected_subjects {
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex: 1 1 auto;
        /* allow to grow and shrink */
        min-height: 0;
        /* important for flex children to allow proper scrolling */
        overflow-y: auto;
        padding-right: 6px;
        /* room for scrollbar */
    }

    /* individual selected item */
    .selected-subject {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        font-weight: 600;
        box-sizing: border-box;
    }

    /* subject text — keep high contrast (white on green) */
    .selected-subject span {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* remove button */
    .remove-btn {
        border: none;
        background: transparent;
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        cursor: pointer;
        padding: 0 6px;
        line-height: 1;
        transition: transform .12s ease;
    }

    .remove-btn:hover {
        transform: scale(1.15) rotate(10deg);
        color: rgba(255, 255, 255, 0.95);
    }

    /* Save/Cancel area remains pinned to bottom; make Save full width if needed */
    .selected-box .mt-3 {
        margin-top: 12px;
    }

    .save-btn {
        background: var(--accent-dark, #e0a800);
        color: #fff;
        border: none;
        border-radius: 10px;
        width: 100%;
        padding: 12px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    }

    .save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.09);
    }


    /* ------------------ SPA addClassPanel ------------------ */

    /* Theme / local variables (change these in one place if needed) */
    :root {
        --panel-bg: #ffffff;
        --panel-radius: 10px;
        --panel-padding: 18px;
        --primary-from: #667eea;
        --primary-to: #764ba2;
        --icon-shadow: rgba(102, 126, 234, 0.12);
        --text-strong: #0f1724;
        --text-muted: #6b7280;
        --divider: #eef2f7;
        --hover-surface: #f3f4f6;
        --focus-outline: rgba(102, 126, 234, 0.18);
        --accent: #ffc107;
        /* your requested theme color */
        --accent-dark: #e0a800;
    }

    /* Panel container */
    .add-class-panel {
        background: var(--panel-bg);
        border-radius: var(--panel-radius);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        padding: var(--panel-padding);
        margin-top: 18px;
        border: 1px solid rgba(0, 0, 0, 0.03);
        overflow: visible;
        position: relative;
        z-index: 900;
        /* prefer transform/opacity animations when possible */
        will-change: transform, opacity;
    }

    /* Optional modifier when you want the panel to behave/look like a page */
    .add-class-panel.fullpage {
        margin: 0;
        padding: 24px;
        border-radius: 0;
        box-shadow: none;
        border-left: none;
        border-right: none;
        width: 100%;
    }

    /* ===== animations ===== */
    @keyframes slideFadeIn {
        from {
            opacity: 0;
            transform: translateY(-12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideFadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-12px);
        }
    }

    /* utility classes used by JS */
    .add-class-panel.is-visible {
        display: block;
    }

    .add-class-panel.anim-slide-in {
        animation: slideFadeIn 360ms cubic-bezier(.22, .9, .36, 1) forwards;
    }

    .add-class-panel.anim-slide-out {
        animation: slideFadeOut 220ms cubic-bezier(.4, 0, .2, 1) forwards;
    }

    /* Header: layout + divider */
    .add-class-panel .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--divider);
        margin-bottom: 12px;
    }

    /* Left group */
    .add-class-panel .panel-header .d-flex {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Icon (circular gradient with shadow) */
    .add-class-panel .panel-icon {
        width: 46px;
        height: 46px;
        display: inline-grid;
        place-items: center;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--primary-from) 0%, var(--primary-to) 100%);
        color: #fff;
        box-shadow: 0 8px 22px var(--icon-shadow);
        flex: 0 0 46px;
        font-size: 30px;
        /* avoid !important */
    }

    /* Title block */
    .add-class-panel .panel-title-wrap {
        margin-left: 6px;
    }

    .add-class-panel .panel-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-strong);
        line-height: 1.05;
        letter-spacing: -0.2px;
    }

    .add-class-panel .panel-subtitle {
        margin: 6px 0 0;
        color: var(--text-muted);
        font-size: 13px;
        max-width: 56ch;
        line-height: 1.35;
    }

    /* Actions / close button */
    .add-class-panel .panel-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #closeAddPanel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #4b5563;
        cursor: pointer;
        transition: background .12s ease, transform .12s ease, box-shadow .12s ease;
        padding: 0;
        font-size: 16px;
    }

    #closeAddPanel:hover {
        background: var(--hover-surface);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(2, 6, 23, 0.06);
    }

    /* Panel body polish */
    .add-class-panel .panel-body {
        padding-top: 12px !important;
    }

    /* Focus states for keyboard users */
    #closeAddPanel:focus,
    .add-class-panel .panel-icon:focus {
        outline: 3px solid var(--focus-outline);
        outline-offset: 2px;
        border-radius: 8px;
    }

    /* Small helper button style (if used inside panel) */
    .add-class-panel .info-btn {
        background: var(--accent);
        border: none;
        border-radius: 50%;
        padding: 6px 8px;
        cursor: pointer;
        color: #1a1a1a;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    /* Responsive rules */
    @media (max-width: 760px) {
        .add-class-panel .panel-header {
            gap: 10px;
            padding-bottom: 10px;
        }

        .add-class-panel .panel-title {
            font-size: 18px;
        }

        .add-class-panel .panel-subtitle {
            font-size: 12px;
            max-width: 40ch;
        }

        .add-class-panel .panel-icon {
            width: 40px;
            height: 40px;
            font-size: 22px;
        }

        #closeAddPanel {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .add-class-panel {
            padding: 14px;
        }
    }

    /* ===== small accessibility + polish helpers ===== */
    /* Make the header icon and close button keyboard tabbable look neat */
    .add-class-panel .panel-icon,
    #closeAddPanel {
        -webkit-tap-highlight-color: transparent;
    }

    /* If you use the .accent-outline class somewhere to highlight a field */
    .add-class-panel .accent-outline {
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.12);
        border-radius: 8px;
    }

    /* End of cleaned add-class-panel CSS */



    /* ================= BACKDROP FOR addClassPanel ================= */

    .class-panel-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
        /* soft dim */
        backdrop-filter: blur(2px);
        /* premium look */
        opacity: 0;
        visibility: hidden;
        transition: opacity 280ms ease, visibility 0ms linear 280ms;
        z-index: 899;
        /* below panel, above page */
    }

    /* When visible */
    .class-panel-backdrop.visible {
        opacity: 1;
        visibility: visible;
        transition: opacity 280ms ease;
    }

    /* Make sure panel is above backdrop */
    .add-class-panel {
        position: relative;
        z-index: 900;
    }



    /* ------------------ Assessment modal (retained) ------------------ */
    #assessmentInfoModal .modal-dialog {
        max-width: 720px;
        margin: 40px auto;
    }

    #assessmentInfoModal .modal-content {
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 40px rgba(34, 41, 47, 0.12);
        font-size: 1.05rem;
        line-height: 1.6;
    }

    #assessmentInfoModal .modal-header {
        padding: 18px 26px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-bottom: none;
    }

    #assessmentInfoModal .modal-body {
        padding: 20px 26px;
        max-height: calc(100vh - 240px);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* keep the panel-invalid highlight rule (used by JS) */
    .category-invalid {
        border: 2px solid #dc3545 !important;
        border-radius: 6px;
        padding: 8px;
    }
</style>