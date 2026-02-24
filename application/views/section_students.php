<div class="content-wrapper">
    <section class="content section-students-page">

        <!-- TOP HEADER ROW -->
        <div class="top-header">
            <h2 class="page-title">Class Management</h2>

            <button class="btn btn-warning btn-lg  selected-class-btn" disabled>
                <?= htmlspecialchars($class_name) ?>
            </button>
        </div>


        <!-- SECTION NAV -->
        <div class="section-nav position-relative mb-4">

            <!-- PREV -->
            <button id="prevSection" class="nav-arrow left">
                <i class="fa fa-chevron-left"></i>
            </button>

            <!-- SECTION NAME (CENTERED) -->
            <div class="section-name">
                <?= htmlspecialchars($section_name) ?>
            </div>

            <!-- NEXT -->
            <button id="nextSection" class="nav-arrow right">
                <i class="fa fa-chevron-right"></i>
            </button>

        </div>




        <!-- STUDENTS CARD -->
        <div class="students-card">
            <div class="students-card-header">
                <div>
                    <!-- SINGLE SOURCE OF TITLE -->
                    <h2 class="mb-0" id="sectionMainTitle">Students</h2>

                    <!-- Strength only for students view -->
                    <h4 id="strengthWrapper">
                        <small class="text-muted">
                            Total Strength :
                            <span id="totalStrengthValue">0</span>
                        </small>
                    </h4>
                </div>

                <div class="students-actions">

                    <!-- SEARCH (students only) -->
                    <div class="search-box search-box-lg position-relative" id="studentSearchWrapper">
                        <input type="text" id="studentSearchInput" class="form-control"
                            placeholder="Search by name or ID">
                        <i class="fa fa-search search-icon"></i>
                    </div>

                    <button class="icon-btn" id="openSectionSettings">
                        <i class="fa fa-cog"></i>
                    </button>

                    <!-- TOGGLE BUTTON -->
                    <button class="btn btn-warning btn-lg" id="toggleTimetableBtn" data-view="students">
                        Time table
                        <i class="fa fa-table"></i>
                    </button>

                </div>
            </div>

            <!-- DYNAMIC CONTENT -->
            <div id="sectionContent">
                <!-- Students OR Timetable loads here -->
            </div>
        </div>


        <!-- SECTION SETTINGS MODAL -->
        <div class="modal fade" id="sectionSettingsModal" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content section-settings-modal">

                    <!-- HEADER -->
                    <div class="modal-header border-0">
                        <div class="header-wrap">
                            <div class="icon-wrap">
                                <i class="fa fa-users"></i>
                            </div>

                            <div>
                                <h5 class="modal-title">Section Strength</h5>
                                <p class="modal-subtitle">
                                    Define maximum allowed students
                                </p>
                            </div>
                        </div>

                        <button type="button" class="close close-btn" data-dismiss="modal">
                            ×
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">
                        <label class="input-label">
                            Maximum Student Strength
                        </label>

                        <input type="number"
                            id="maxStrengthInput"
                            class="form-control strength-input"
                            min="1"
                            placeholder="100">
                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer">
                        <button class="btn btn-light btn-sm px-4" data-dismiss="modal">
                            Cancel
                        </button>

                        <button class="btn btn-warning btn-sm px-4" id="saveSectionSettings">
                            Save Changes
                        </button>
                    </div>

                </div>
            </div>
        </div>



        <div class="modal fade" id="transferStudentsModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content transfer-modal">

                    <!-- HEADER -->
                    <div class="modal-header  position-relative">
                        <h5 class="modal-title text-right w-100 pr-4">
                            <strong>
                                Transfer Students
                            </strong>
                        </h5>
                        <button class="close position-absolute" style="right: 15px;"
                            data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <div class="transfer-flow">

                            <!-- FROM -->
                            <div class="transfer-box from-box">

                                <h6 class="text-muted mb-2">From</h6>

                                <div class="form-group mb-2">
                                    <label class="small">Class</label>
                                    <div class="form-control bg-light" readonly>
                                        <strong id="fromClassLabel"></strong>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small">Section</label>
                                    <div class="form-control bg-light" readonly>
                                        <span id="fromSectionLabel"></span>
                                    </div>
                                </div>

                            </div>

                            <!-- ARROW -->
                            <div class="transfer-arrow">
                                <i class="fa fa-long-arrow-right"></i>
                            </div>

                            <!-- TO -->
                            <div class="transfer-box to-box">

                                <h6 class="text-muted mb-2">To</h6>

                                <div class="form-group mb-2">
                                    <label class="small">Target Class</label>
                                    <select id="targetClass" class="form-control"></select>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small">Target Section</label>
                                    <select id="targetSection" class="form-control"></select>
                                </div>

                            </div>

                        </div>

                        <p class="text-muted small mt-3 mb-0 text-center">
                            Selected students will be moved to the chosen class & section.
                        </p>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-danger btn-sm" id="confirmTransfer">
                            Confirm Transfer
                        </button>
                    </div>

                </div>
            </div>
        </div>





    </section>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    /* ============================================================
   GLOBAL STATE (SINGLE SOURCE OF TRUTH)
============================================================ */
    const CLASS_NAME = <?= json_encode($class_name) ?>;
    const SECTION_NAME = <?= json_encode($section_name) ?>;

    let CURRENT_VIEW = 'students';
    let ALL_SECTIONS = [];
    let CURRENT_INDEX = -1;

    let TIMETABLE_EDIT_MODE = false;
    let TIMETABLE_READY = false;
    let TIMETABLE_BACKUP = null;
    let ORIGINAL_SECTION_STRENGTH = null;

    /* Globals used across partials */
    window.CURRENT_CLASS_NAME = CLASS_NAME;
    window.CURRENT_SECTION_NAME = SECTION_NAME;
    window.ALL_SUBJECTS_CACHE = [];
    window.CURRENT_CELL = null;

    window.ignoreBlur = false;

    // 🔥 SUBJECTS USED IN TIMETABLE (SINGLE SOURCE OF TRUTH)
    window.SELECTED_SUBJECTS_SET = new Set();


    /* ============================================================
       INIT
    ============================================================ */
    $(document).ready(function() {
        fetchStudents();
        fetchSectionStrength();
        loadSectionsForNav();
    });

    /* ============================================================
       SECTION STRENGTH
    ============================================================ */
    function fetchSectionStrength() {
        $.post(
            BASE_URL + 'classes/get_section_settings', {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME
            },
            res => {
                $('#totalStrengthValue').text(
                    res && res.max_strength !== undefined ? res.max_strength : 0
                );
            },
            'json'
        );
    }


    /* ============================================================
   LOAD CURRENT SECTION STRENGTH INTO MODAL
============================================================ */
    function loadCurrentSectionStrength() {

        // Clear first (prevents stale values)
        $('#maxStrengthInput').val('').prop('disabled', true);

        $.post(
            BASE_URL + 'classes/get_section_settings', {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME
            },
            function(res) {

                if (res && res.max_strength !== undefined) {
                    ORIGINAL_SECTION_STRENGTH = res.max_strength;
                    $('#maxStrengthInput').val(res.max_strength);
                }

                // Enable + focus input
                $('#maxStrengthInput')
                    .prop('disabled', false)
                    .focus()
                    .select();

            },
            'json'
        );
    }
    $(document).on('input', '#maxStrengthInput', function() {
        const current = parseInt(this.value, 10);
        $('#saveSectionSettings').prop(
            'disabled',
            current === ORIGINAL_SECTION_STRENGTH
        );
    });



    /* ============================================================
       SECTION NAVIGATION
    ============================================================ */
    function loadSectionsForNav() {
        $.post(
            BASE_URL + 'classes/fetch_class_sections', {
                class_name: CLASS_NAME
            },
            sections => {
                if (!Array.isArray(sections)) return;
                ALL_SECTIONS = sections.map(s => s.name);
                CURRENT_INDEX = ALL_SECTIONS.indexOf(SECTION_NAME);
                updateNavButtons();
            },
            'json'
        );
    }

    function updateNavButtons() {
        $('#prevSection').prop('disabled', CURRENT_INDEX <= 0);
        $('#nextSection').prop('disabled', CURRENT_INDEX >= ALL_SECTIONS.length - 1);
    }

    $('#prevSection').on('click', () => CURRENT_INDEX > 0 && navigateToIndex(CURRENT_INDEX - 1));
    $('#nextSection').on('click', () => CURRENT_INDEX < ALL_SECTIONS.length - 1 && navigateToIndex(CURRENT_INDEX + 1));

    function navigateToIndex(index) {
        if (!ALL_SECTIONS[index]) return;

        const cls = CLASS_NAME.replace('Class ', '');
        const sec = ALL_SECTIONS[index].replace('Section ', '');

        window.location.href =
            "<?= base_url('classes/section_students/') ?>" +
            encodeURIComponent(cls) + '/' +
            encodeURIComponent(sec);
    }

    /* ============================================================
       STUDENTS VIEW
    ============================================================ */
    function fetchStudents() {
        $.post(
            BASE_URL + 'classes/fetch_section_students', {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME
            },
            renderStudentsTable,
            'json'
        );
    }

    function renderStudentsTable(students) {
        if (!Array.isArray(students) || !students.length) {
            $('#sectionContent').html('<p class="text-muted">No students found.</p>');
            return;
        }

        let html = `
    <table class="table table-borderless students-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAllStudents"></th>
                <th>S.No</th>
                <th>Student Photo</th>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Last result</th>
                <th>Phone No.</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>
    `;

        students.forEach((s, i) => {
            html += `
        <tr>
            <td>
                <input type="checkbox"
                       class="student-checkbox"
                       data-id="${s.id}">
            </td>
            <td>${i + 1}</td>
            <td>
                <img src="${s.photo || '<?= base_url("assets/avatar.png") ?>'}"
                     class="student-avatar">
            </td>
            <td>${s.name}</td>
            <td>${s.id}</td>
            <td>${s.last_result}</td>
            <td>${s.phone}</td>
            <td>
                <button class="btn btn-info btn-sm view-student"
                        data-id="${s.id}">
                    <i class="fa fa-eye"></i>
                </button>
            </td>
        </tr>
        `;
        });

        html += `
        </tbody>

        <tfoot>
            <tr>
                <td colspan="8">
                    <div class="d-flex justify-content-end pt-3 w-100">
                        <button class="btn btn-danger btn-lg"
                                id="transferStudentsBtn"
                                disabled>
                            Transfer Students
                            <i class="fa fa-exchange-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tfoot>


    </table>
    `;

        $('#sectionContent').html(html);
        $('#selectAllStudents').prop('checked', false);
        updateTransferButtonState();

    }


    /* ============================================================
   STUDENT SEARCH (NAME / ID)
============================================================ */
    $(document).on('keyup', '#studentSearchInput', function() {

        const query = $(this).val().trim().toLowerCase();
        let visibleCount = 0;

        // Loop through student rows
        $('.students-table tbody tr').each(function() {

            const name = $(this).find('td:nth-child(4)').text().toLowerCase(); // Student Name
            const id = $(this).find('td:nth-child(5)').text().toLowerCase(); // Student ID

            if (!query || name.includes(query) || id.includes(query)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        // Handle "no results"
        const $tbody = $('.students-table tbody');

        $tbody.find('.no-search-results').remove();

        if (query && visibleCount === 0) {
            $tbody.append(`
            <tr class="no-search-results">
                <td colspan="8" class="text-center text-muted py-3">
                    No matching students found
                </td>
            </tr>
        `);
        }
    });

    $(document).on('keydown', '#studentSearchInput', function(e) {
        if (e.key === 'Escape') {
            $(this).val('');
            $('.students-table tbody tr').show();
            $('.no-search-results').remove();
        }
    });




    $(document).on('click', '.view-student', function() {
        var studentId = $(this).data('id');
        window.location.href = "<?= base_url('student/student_profile') ?>/" + studentId;
    });


    function updateTransferButtonState() {
        const selectedCount = $('.student-checkbox:checked').length;

        $('#transferStudentsBtn').prop('disabled', selectedCount === 0);
    }



    $(document).on('change', '.student-checkbox', function() {

        const total = $('.student-checkbox').length;
        const checked = $('.student-checkbox:checked').length;

        // Sync "Select All" checkbox
        $('#selectAllStudents').prop('checked', total === checked);

        updateTransferButtonState();
    });

    $(document).on('change', '#selectAllStudents', function() {
        $('.student-checkbox').prop('checked', this.checked);
        updateTransferButtonState();
    });




    function loadClassesForTransfer() {

        $.post(
            BASE_URL + 'classes/loadClassesForTransfer', {},
            function(res) {

                if (!res || typeof res !== 'object') return;

                const classes = res.classes || {};
                const sectionsMap = res.sections || {};

                const $classSelect = $('#targetClass').empty();
                const $sectionSelect = $('#targetSection').empty();

                // ✅ Filter valid classes (must have real sections)
                const validClasses = Object.keys(classes).filter(className => {
                    const sections = sectionsMap[className];
                    return Array.isArray(sections) &&
                        sections.length > 0 &&
                        sections.some(sec => sec.startsWith('Section '));
                });

                // Populate class dropdown (ONLY valid classes)
                validClasses.forEach(className => {
                    $classSelect.append(
                        `<option value="${className}">${className}</option>`
                    );
                });

                // Load sections for first valid class
                if (validClasses.length) {
                    populateSections(sectionsMap[validClasses[0]]);
                } else {
                    populateSections([]);
                }

                // On class change → reload sections
                $classSelect.off('change').on('change', function() {
                    populateSections(sectionsMap[this.value] || []);
                });
            },
            'json'
        );
    }


    function populateSections(sections) {

        const $sectionSelect = $('#targetSection').empty();

        if (!Array.isArray(sections) || !sections.length) {
            $sectionSelect.append(`<option value="">No Sections</option>`);
            return;
        }

        sections.forEach(sec => {
            $sectionSelect.append(
                `<option value="${sec}">${sec}</option>`
            );
        });
    }



    $(document).on('click', '#transferStudentsBtn', function() {

        if (this.disabled) return;

        $('#fromClassLabel').text(CLASS_NAME);
        $('#fromSectionLabel').text(SECTION_NAME);

        loadClassesForTransfer();
        $('#transferStudentsModal').modal('show');
    });


    function getSelectedStudentIds() {
        return $('.student-checkbox:checked')
            .map((i, el) => $(el).data('id'))
            .get();
    }


    $(document).on('click', '#confirmTransfer', function() {

        const $btn = $(this);
        if ($btn.prop('disabled')) return;

        const studentIds = $('.student-checkbox:checked')
            .map((i, el) => $(el).data('id'))
            .get();

        if (!studentIds.length) {
            alert('Please select at least one student.');
            return;
        }

        const payload = {
            student_ids: studentIds,
            from_class: CLASS_NAME,
            from_section: SECTION_NAME,
            to_class: $('#targetClass').val(),
            to_section: $('#targetSection').val()
        };

        // 🔒 Prevent double submit
        $btn.prop('disabled', true).text('Transferring...');

        $.post(
            BASE_URL + 'classes/transfer_students',
            payload,
            function(res) {

                if (res.status === 'success') {

                    $('#transferStudentsModal').modal('hide');

                    // Reset UI
                    $('.student-checkbox, #selectAllStudents').prop('checked', false);
                    updateTransferButtonState();

                    // Reload students of CURRENT section
                    fetchStudents();

                } else {
                    alert(res.message || 'Transfer failed.');
                }
            },
            'json'
        ).always(function() {
            $btn.prop('disabled', false).text('Confirm Transfer');
        });
    });



    /* ============================================================
       TOGGLE STUDENTS ↔ TIMETABLE
    ============================================================ */
    $(document).on('click', '#toggleTimetableBtn', function() {

        if (CURRENT_VIEW === 'students') {
            CURRENT_VIEW = 'timetable';
            $('#sectionMainTitle').text('Time table');
            $('#strengthWrapper, #studentSearchWrapper').hide();
            $(this).html('Student List <i class="fa fa-users"></i>');
            loadTimetable();
        } else {
            CURRENT_VIEW = 'students';
            $('#sectionMainTitle').text('Students');
            $('#strengthWrapper, #studentSearchWrapper').show();
            $('#studentSearchInput').val('');
            $(this).html('Time table <i class="fa fa-table"></i>');
            fetchStudents();
        }
    });

    /* ============================================================
       LOAD TIMETABLE
    ============================================================ */
    function loadTimetable() {
        TIMETABLE_READY = false;

        $.post(
            BASE_URL + 'classes/load_timetable_partial', {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME
            },
            html => {
                $('#sectionContent').html(html);
                fetchTimetableSettingsAndBuild();
            }
        );
    }

    function fetchTimetableSettingsAndBuild() {
        $.getJSON(
            BASE_URL + 'classes/get_timetable_settings',
            res => {
                if (!res?.Start_time || !res?.End_time) return;
                buildTimetable(res);
                loadSavedTimetable();
            }
        );
    }

    /* ============================================================
       BUILD & RENDER TIMETABLE
    ============================================================ */
    function buildTimetable(settings) {

        const start = ampmToMinutes(settings.Start_time);
        const periodLength = parseFloat(settings.Length_of_period);
        const totalPeriods = parseInt(settings.No_of_periods, 10);
        const recesses = Array.isArray(settings.Recesses) ?
            settings.Recesses : [];

        if (!start || !periodLength || !totalPeriods) {
            console.warn('Invalid timetable settings', settings);
            return;
        }

        let slots = [];
        let pointer = start;

        for (let p = 1; p <= totalPeriods; p++) {

            // PERIOD
            const next = Math.round(pointer + periodLength);

            slots.push({
                type: 'period',
                from: minutesToAMPM(pointer),
                to: minutesToAMPM(next)
            });

            pointer = next;

            // RECESS AFTER THIS PERIOD?
            const recess = recesses.find(r => r.after_period === p);

            if (recess && recess.duration > 0) {
                const breakEnd = pointer + recess.duration;

                slots.push({
                    type: 'break',
                    from: minutesToAMPM(pointer),
                    to: minutesToAMPM(breakEnd)
                });

                pointer = breakEnd;
            }
        }

        renderTimetable(slots);
        TIMETABLE_READY = true;
    }



    function renderTimetable(slots) {

        const $grid = $('#timetableGrid').empty();

        /* ===== HEADER ===== */
        let header = `
        <div class="tt-row tt-head">
            <div class="tt-cell day-time-head">
                <span class="day-label">Day</span>
                <span class="time-label">Time</span>
            </div>
    `;

        slots.forEach(s => {
            header += `
            <div class="tt-cell time-head ${s.type === 'break' ? 'break-head' : ''}">
                ${s.from} - ${s.to}
            </div>
        `;
        });

        header += `</div>`;
        $grid.append(header);

        /* ===== BODY ===== */
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        days.forEach(day => {

            let row = `
            <div class="tt-row">
                <div class="tt-cell day">${day}</div>
        `;

            slots.forEach(s => {

                if (s.type === 'break') {
                    row += `<div class="tt-cell break-cell">BREAK</div>`;
                } else {
                    row += `<div class="tt-cell subject">Select subject</div>`;
                }

            });

            row += `</div>`;
            $grid.append(row);
        });
    }


    /* ============================================================
       LOAD & APPLY SAVED TIMETABLE
    ============================================================ */
    function loadSavedTimetable() {
        $.post(
            BASE_URL + 'classes/load_timetable', {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME
            },
            saved => saved && TIMETABLE_READY && applySavedTimetable(saved),
            'json'
        );
    }



    //   function applySavedTimetable(saved) {

    //     if (!saved || typeof saved !== 'object') return;

    //     $('.tt-row:not(.tt-head)').each(function () {

    //         const day = $(this).find('.day').text().trim();
    //         const daySlots = saved[day];
    //         if (!Array.isArray(daySlots)) return;

    //         const map = {};
    //         daySlots.forEach(s => {
    //             map[s.time] = s.subject;
    //         });

    //         let slotIndex = 0;
    //         const slots = getTimeSlots();

    //         $(this).find('.tt-cell').each(function (idx) {

    //             if (idx === 0) return;

    //             if ($(this).hasClass('break-cell')) {
    //                 const t = slots[slotIndex];
    //                 if (map[t]) {
    //                     $(this).text('BREAK');
    //                 }
    //                 return;
    //             }

    //             const time = slots[slotIndex++];
    //             if (map[time]) {
    //                 $(this).text(map[time]);
    //             }
    //         });
    //     });
    // }




    $(document).on('click', '#saveTimetableEdit', function() {

        if (!TIMETABLE_EDIT_MODE) return;

        const timetable = collectTimetableData();

        if (!Object.keys(timetable).length) {
            alert('Please assign at least one subject before saving.');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');

        $.post(
            BASE_URL + 'classes/save_timetable', {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME,
                timetable: JSON.stringify(timetable)
            },
            function(res) {

                if (res.status === 'success') {

                    // ✅ Exit edit mode
                    TIMETABLE_EDIT_MODE = false;
                    TIMETABLE_BACKUP = null;
                    window.CURRENT_CELL = null;

                    $('#timetableEditActions').addClass('hidden');
                    $('#editTimetableBtn').removeClass('hidden');
                    $('.timetable-wrapper').removeClass('edit-mode');

                    // ✅ Reload saved timetable
                    loadSavedTimetable();

                } else {
                    alert(res.message || 'Failed to save timetable');
                }
            },
            'json'
        ).always(function() {
            $btn.prop('disabled', false).text('Save');
        });
    });


    function calculatePeriodLength() {

        $('.period-skeleton').removeClass('hidden');
        $('#ttPeriodLength').val('');

        setTimeout(() => {

            const start = $('#ttStartTime').val();
            const end = $('#ttEndTime').val();
            const periods = parseInt($('#ttNoOfPeriod').val(), 10);

            if (!start || !end || !periods || periods <= 0) {
                $('.period-skeleton').addClass('hidden');
                return;
            }

            const startMin = toMinutes(start);
            const endMin = toMinutes(end);

            let recessTotal = 0;
            $('.recess-duration').each(function() {
                const d = parseInt(this.value, 10);
                if (d > 0) recessTotal += d;
            });

            const available = endMin - startMin - recessTotal;

            if (available <= 0) {
                alert('Recess time exceeds available duration');
                $('.period-skeleton').addClass('hidden');
                return;
            }

            const len = (available / periods).toFixed(1);
            $('#ttPeriodLength').val(len);

            $('.period-skeleton').addClass('hidden');

        }, 300);
    }


    $(document).on(
        'change',
        '#ttStartTime, #ttEndTime, #ttNoOfPeriod, .recess-after, .recess-duration',
        calculatePeriodLength
    );




    function toMinutes(t) {
        const [h, m] = t.split(':').map(Number);
        return (h * 60) + m;
    }




    $(document).on('click', '#cancelTimetableEdit', function() {

        // Restore previous timetable if backup exists
        if (TIMETABLE_BACKUP) {
            applySavedTimetable(TIMETABLE_BACKUP);
        }

        // Reset edit state cleanly
        TIMETABLE_EDIT_MODE = false;
        TIMETABLE_BACKUP = null;
        window.CURRENT_CELL = null;

        // Restore UI
        $('#timetableEditActions').addClass('hidden');
        $('#editTimetableBtn').removeClass('hidden');
        $('.timetable-wrapper').removeClass('edit-mode');
    });



    $(document).on('click', '#editTimetableBtn', function() {
        TIMETABLE_EDIT_MODE = true;
        TIMETABLE_BACKUP = collectTimetableData();

        $('#editTimetableBtn').addClass('hidden');
        $('#timetableEditActions').removeClass('hidden');
        $('.timetable-wrapper').addClass('edit-mode'); // 🔥 REQUIRED
    });



    $(document).on('click', '.tt-cell.subject', function() {
        if (!TIMETABLE_EDIT_MODE || $(this).hasClass('break-cell')) return;

        window.CURRENT_CELL = $(this);

        const existing = $(this).attr('data-subject-name');
        if (existing) {
            window.SELECTED_SUBJECTS_SET.add(existing);
        }

        loadSubjectsForTimetable();
        $('#subjectSelectModal').modal('show');
    });



    /* ============================================================
       SUBJECT SELECTION (REQUIRED FOR EDIT MODE)
    ============================================================ */

    function loadSubjectsForTimetable() {
        $.post(
            BASE_URL + 'classes/fetch_subjects_for_timetable', {
                class_name: CLASS_NAME
            },
            renderSubjectModal,
            'json'
        );
    }



    // function renderSubjectModal(data) {

    //     if (!data || typeof data !== 'object') return;

    //     // ✅ SET CLASS TITLE (🔥 THIS WAS MISSING)
    //     $('#classSubjectTitle').text(CURRENT_CLASS_NAME);

    //     window.ALL_SUBJECTS_CACHE = data.all_subjects || [];
    //     $('#allSubjectCount').text(
    //         `(${window.ALL_SUBJECTS_CACHE.length})`
    //     );

    //     const $classBox = $('.class-subjects').empty();
    //     const $allBox = $('.all-subjects').empty();

    //     /* ==============================
    //        CLASS SUBJECTS
    //     ============================== */
    //     if (Array.isArray(data.class_subjects) && data.class_subjects.length) {

    //         data.class_subjects.forEach(sub => {
    //             $classBox.append(`
    //             <button type="button"
    //                     class="subject-item outline class-subject">
    //                 ${sub.name}
    //             </button>
    //         `);
    //         });

    //     } else {
    //         $classBox.html('<span class="text-muted">No class subjects</span>');
    //     }

    //     /* ==============================
    //        ALL SUBJECTS
    //     ============================== */
    //     renderAllSubjects(window.ALL_SUBJECTS_CACHE);
    // }


    function renderSubjectModal(data) {

        if (!data || typeof data !== 'object') return;

        $('#classSubjectTitle').text(CURRENT_CLASS_NAME);

        window.ALL_SUBJECTS_CACHE = data.all_subjects || [];
        $('#allSubjectCount').text(`(${window.ALL_SUBJECTS_CACHE.length})`);

        const $classBox = $('.class-subjects').empty();

        if (Array.isArray(data.class_subjects) && data.class_subjects.length) {
            data.class_subjects.forEach(sub => {
                $classBox.append(`
                <button type="button"
                        class="subject-item outline class-subject">
                    ${sub.name}
                </button>
            `);
            });
        } else {
            $classBox.html('<span class="text-muted">No class subjects</span>');
        }

        renderAllSubjects(window.ALL_SUBJECTS_CACHE);

        // 🔥 FIX: restore selected subject
        if (window.SELECTED_SUBJECT_NAME) {
            $('.subject-item').each(function() {
                const name = $(this).text().trim();
                if (window.SELECTED_SUBJECTS_SET.has(name)) {
                    $(this).addClass('selected');
                }
            });
        }
    }




    // function renderAllSubjects(subjects) {

    //     const $grid = $('.all-subjects').empty();

    //     if (!Array.isArray(subjects) || !subjects.length) {
    //         $grid.html('<span class="text-muted">No subjects found</span>');
    //         return;
    //     }

    //     subjects.forEach(sub => {
    //         $grid.append(
    //             `<button type="button" class="subject-item outline">${sub.name}</button>`
    //         );
    //     });
    // }


    function renderAllSubjects(subjects) {

        const $grid = $('.all-subjects').empty();

        if (!Array.isArray(subjects) || !subjects.length) {
            $grid.html('<span class="text-muted">No subjects found</span>');
            return;
        }

        subjects.forEach(sub => {
            const isSelected = window.SELECTED_SUBJECTS_SET.has(sub.name);

            $grid.append(`
            <button type="button"
                class="subject-item outline ${isSelected ? 'selected' : ''}">
                ${sub.name}
            </button>
        `);
        });
    }



    function enterSearchMode() {
        $('.class-subjects').hide();
        $('.all-subjects').show();
    }

    function exitSearchMode() {
        $('.class-subjects').show();
    }

    // Click on search icon
    $(document).on('click', '.subject-search-icon', function() {
        enterSearchMode();
        renderAllSubjects(window.ALL_SUBJECTS_CACHE);
        $('#subjectSearch').focus();
    });

    // Focus search input
    $(document).on('focus', '#subjectSearch', function() {
        enterSearchMode();
        renderAllSubjects(window.ALL_SUBJECTS_CACHE);
    });

    // Typing search
    $(document).on('keyup', '#subjectSearch', function() {
        const q = $(this).val().trim().toLowerCase();

        if (!q) {
            exitSearchMode();
            renderAllSubjects(window.ALL_SUBJECTS_CACHE);
            return;
        }

        const filtered = window.ALL_SUBJECTS_CACHE.filter(sub =>
            sub.name.toLowerCase().includes(q)
        );

        renderAllSubjects(filtered);
    });

    // Prevent blur before click
    $(document).on('mousedown', '.subject-item', function() {
        window.ignoreBlur = true;
    });

    // Blur logic (restore state safely)
    $(document).on('blur', '#subjectSearch', function() {
        setTimeout(() => {
            // if (!window.ignoreBlur && !$('#subjectSearch').val().trim()) {
            if (!window.ignoreBlur && document.activeElement !== $('#subjectSearch')[0]) {
                exitSearchMode();
                renderAllSubjects(window.ALL_SUBJECTS_CACHE);
            }
            window.ignoreBlur = false;
        }, 150);
    });




    /* ============================================================
       SUBJECT PICK
    ============================================================ */
    $(document).on('click', '.subject-item', function() {
        if (!window.CURRENT_CELL) return;

        const name = $(this).text().trim();

        // ✅ APPLY TO TIMETABLE CELL
        window.CURRENT_CELL.text(name);
        window.CURRENT_CELL.attr('data-subject-name', name);

        // ✅ STORE IN GLOBAL SET
        window.SELECTED_SUBJECTS_SET.add(name);

        // ✅ MARK THIS BUTTON AS SELECTED
        $(this).addClass('selected');

        $('#subjectSelectModal').modal('hide');
    });





    /* ============================================================
       SETTINGS MODALS (SINGLE CONTROLLER)
    ============================================================ */
    $(document).on('click', '#openSectionSettings', function() {

        if (CURRENT_VIEW === 'students') {
            $('#sectionSettingsModal').modal('show');
            // fetchSectionStrength();
            loadCurrentSectionStrength();
            return;
        }

        $('#timetableSettingsModal').modal('show');
        fetchTimetableSettings();
    });

    /* ============================================================
       SAVE SECTION STRENGTH
    ============================================================ */
    $(document).on('click', '#saveSectionSettings', function() {

        const maxStrength = $('#maxStrengthInput').val();
        if (!maxStrength || maxStrength <= 0) return alert('Invalid value');

        $.post(
            "<?= base_url('classes/save_section_settings') ?>", {
                class_name: CLASS_NAME,
                section_name: SECTION_NAME,
                max_strength: maxStrength
            },
            res => {
                if (res.status === 'success') {
                    $('#sectionSettingsModal').modal('hide');
                    $('#totalStrengthValue').text(maxStrength);
                    ORIGINAL_SECTION_STRENGTH = null;
                }
            },
            'json'
        );
    });

    /* ============================================================
       TIMETABLE SETTINGS (FETCH + SAVE)
    ============================================================ */
    // function fetchTimetableSettings() {
    //     $.getJSON(
    //         BASE_URL + 'classes/get_timetable_settings',
    //         function(res) {

    //             if (!res || typeof res !== 'object') return;

    //             // ✅ Set core fields
    //             $('#ttStartTime').val(ampmTo24(res.Start_time || ''));
    //             $('#ttEndTime').val(ampmTo24(res.End_time || ''));
    //             $('#ttPeriodLength').val(res.Length_of_period || '');
    //             $('#ttNoOfPeriod').val(res.No_of_periods || '');

    //             // ✅ CRITICAL FIX: clear before append
    //             $('#recessContainer').empty();

    //             // ✅ Load recesses from Firebase
    //             if (Array.isArray(res.Recess_breaks)) {
    //                 res.Recess_breaks.forEach(range => {

    //                     if (!range || !range.includes(' - ')) return;

    //                     const [from, to] = range.split(' - ');

    //                     addRecessRow(
    //                         ampmTo24(from.trim()),
    //                         ampmTo24(to.trim())
    //                     );
    //                 });
    //             }
    //         }
    //     );
    // }

    function fetchTimetableSettings() {

        $.getJSON(
            BASE_URL + 'classes/get_timetable_settings',
            function(res) {

                if (!res) return;

                $('#ttStartTime').val(ampmTo24(res.Start_time || ''));
                $('#ttEndTime').val(ampmTo24(res.End_time || ''));
                $('#ttNoOfPeriod').val(res.No_of_periods || '');
                $('#ttPeriodLength').val(res.Length_of_period || '');

                $('#recessContainer').empty();

                // ✅ FIX: load recess AFTER PERIOD correctly
                if (Array.isArray(res.Recesses)) {
                    res.Recesses.forEach(r => {
                        addRecessRow(
                            r.after_period ?? '',
                            r.duration ?? ''
                        );
                    });
                }

                // 🔁 recalc period length after load
                calculatePeriodLength();
            }
        );
    }


    /* ============================================================
       RECESS HANDLERS
    ============================================================ */

    $(document).on('click', '#addRecessBtn', () => addRecessRow());

    $(document).on('click', '.removeRecessBtn', function() {
        $(this).closest('.recess-row').remove();
        calculatePeriodLength();
    });




    function addRecessRow(afterPeriod = '', duration = '') {

        const totalPeriods = parseInt($('#ttNoOfPeriod').val(), 10) || 0;

        let periodOptions = '<option value="">After period</option>';
        for (let i = 1; i < totalPeriods; i++) {
            periodOptions += `<option value="${i}">${i}</option>`;
        }

        const $row = $(`
        <div class="recess-row d-flex align-items-center gap-2 mb-2">
            <select class="form-control form-control-sm recess-after">
                ${periodOptions}
            </select>

            <input type="number"
                   class="form-control form-control-sm recess-duration"
                   placeholder="Duration (min)"
                   min="5">

            <button type="button"
                    class="btn btn-light btn-sm removeRecessBtn">✕</button>
        </div>
    `);

        // ✅ SET SAVED VALUES
        if (afterPeriod) {
            $row.find('.recess-after').val(String(afterPeriod));
        }

        if (duration) {
            $row.find('.recess-duration').val(duration);
        }

        $('#recessContainer').append($row);
    }



    $(document).on('click', '#saveTimetableSettings', function() {

        const recesses = [];

        $('.recess-row').each(function() {
            const after = parseInt($(this).find('.recess-after').val(), 10);
            const dur = parseInt($(this).find('.recess-duration').val(), 10);

            if (after && dur) {
                recesses.push({
                    after_period: after,
                    duration: dur
                });
            }
        });

        $.post(
            BASE_URL + 'classes/save_timetable_settings', {
                start_time: $('#ttStartTime').val(),
                end_time: $('#ttEndTime').val(),
                no_of_periods: $('#ttNoOfPeriod').val(),
                recesses: recesses
            },
            function(res) {
                if (res.status === 'success') {
                    $('#timetableSettingsModal').modal('hide');
                    fetchTimetableSettingsAndBuild();
                } else {
                    alert(res.message || 'Failed to save timetable');
                }
            },
            'json'
        );
    });



    /* ============================================================
       UTILITIES
    ============================================================ */
    //     function getTimeSlots() {
    //     return $('.tt-head .tt-cell')
    //         .filter((i, e) => !$(e).hasClass('break-head'))
    //         .map((i, e) => $(e).text().trim())
    //         .get();
    // }


    /* ============================================================
       COLLECT TIMETABLE DATA (🔥 REQUIRED FOR EDIT MODE)
    ============================================================ */
    // function collectTimetableData() {

    //     const table = {};
    //     const slots = getTimeSlots();

    //     $('.tt-row:not(.tt-head)').each(function() {

    //         const day = $(this).find('.day').text().trim();
    //         const row = {};
    //         let hasValue = false;
    //         let i = 0;

    //         $(this).find('.tt-cell').each(function(idx) {

    //             if (idx === 0 || $(this).hasClass('break-cell')) return;

    //             const value = $(this).text().trim();
    //             const slot = slots[i++];

    //             if (value && value !== 'Select subject') {
    //                 row[slot] = value;
    //                 hasValue = true;
    //             }
    //         });


    //         if (hasValue) {
    //             table[day] = row;
    //         }
    //     });

    //     return table;
    // }

    function applySavedTimetable(saved) {

        if (!saved || typeof saved !== 'object') return;

        const headerCells = $('.tt-head .tt-cell');

        $('.tt-row:not(.tt-head)').each(function() {

            const day = $(this).find('.day').text().trim();
            const daySlots = saved[day];
            if (!Array.isArray(daySlots)) return;

            const map = {};
            daySlots.forEach(s => {
                map[s.time] = s.subject;
            });

            $(this).find('.tt-cell').each(function(colIndex) {

                if (colIndex === 0) return;

                const time = $(headerCells[colIndex]).text().trim();
                if (!time) return;

                if (map[time]) {
                    $(this).text(map[time]);
                }
            });
        });
    }


    function collectTimetableData() {

        const table = {};

        // cache header cells (time columns)
        const headerCells = $('.tt-head .tt-cell');

        $('.tt-row:not(.tt-head)').each(function() {

            const day = $(this).find('.day').text().trim();
            const rows = [];

            $(this).find('.tt-cell').each(function(colIndex) {

                // skip day column (col 0)
                if (colIndex === 0) return;

                const time = $(headerCells[colIndex]).text().trim();
                if (!time) return;

                // BREAK cell
                if ($(this).hasClass('break-cell')) {
                    rows.push({
                        time,
                        subject: 'BREAK'
                    });
                    return;
                }

                const subject = $(this).text().trim();
                if (subject && subject !== 'Select subject') {
                    rows.push({
                        time,
                        subject
                    });
                }
            });

            if (rows.length) {
                table[day] = rows;
            }
        });

        return table;
    }

    function ampmToMinutes(t) {
        let [time, mod] = t.split(/(AM|PM)/);
        let [h, m] = time.split(':').map(Number);
        if (mod === 'PM' && h !== 12) h += 12;
        if (mod === 'AM' && h === 12) h = 0;
        return h * 60 + m;
    }

    function minutesToAMPM(m) {
        let h = Math.floor(m / 60),
            min = m % 60;
        let mod = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${String(min).padStart(2,'0')} ${mod}`;
    }

    function ampmTo24(t) {
        const m = t?.match(/(\d+):(\d+)(AM|PM)/);
        if (!m) return '';
        let h = +m[1];
        if (m[3] === 'PM' && h !== 12) h += 12;
        if (m[3] === 'AM' && h === 12) h = 0;
        return `${String(h).padStart(2,'0')}:${m[2]}`;
    }
</script>




<style>
    /* ============================
   SECTION STRENGTH MODAL (ERP)
============================ */

    .section-settings-modal {
        border-radius: 20px;
        border: none;
        padding: 10px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
    }

    /* Header layout */
    .header-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    /* Icon */
    .icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, #f4b000, #f5af00);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    /* Title */
    .section-settings-modal .modal-title {
        font-size: 19px;
        font-weight: 600;
        margin: 0;
    }

    /* Subtitle */
    .modal-subtitle {
        font-size: 13px;
        color: #777;
        margin: 2px 0 0;
    }

    /* Close */
    .close-btn {
        font-size: 22px;
        opacity: 0.5;
    }

    .close-btn:hover {
        opacity: 1;
    }

    /* Body */
    .section-settings-modal .modal-body {
        padding: 20px 18px 10px;
    }

    /* Label */
    .input-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
    }

    /* Input */
    .strength-input {
        height: 46px;
        border-radius: 14px;
        font-size: 18px;
        font-weight: 600;
        text-align: center;
        border: 1px solid #ddd;
    }

    .strength-input::placeholder {
        color: #bbb;
    }

    /* Footer */
    .section-settings-modal .modal-footer {
        border-top: none;
        padding: 10px 18px 18px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }



    /* =====================================================
   PAGE WRAPPER
===================================================== */
    .section-students-page {
        background: #f5f6f8;
        padding: 24px;
    }

    /* =====================================================
   TOP HEADER
===================================================== */
    .top-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .page-title {
        font-weight: 600;
        margin: 0;
    }

    .selected-class-btn {
        border-radius: 20px;
        padding: 6px 14px;
    }

    /* =====================================================
   SECTION NAVIGATION
===================================================== */
    .section-nav {
        position: relative;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
    }

    .section-name {
        font-size: 22px;
        font-weight: 600;
        color: #333;
        pointer-events: none;
    }

    /* Arrow buttons */
    .nav-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);

        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: none;

        background: #f0f0f0;
        color: #333;
        font-size: 18px;

        display: flex;
        align-items: center;
        justify-content: center;

        cursor: pointer;
        transition: all 0.2s ease;
    }

    .nav-arrow.left {
        left: 0;
    }

    .nav-arrow.right {
        right: 0;
    }

    .nav-arrow:hover:not(:disabled) {
        background: #f5af00;
        color: #fff;
    }

    .nav-arrow:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    /* =====================================================
   STUDENTS CARD
===================================================== */
    .students-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
    }

    .students-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    /* =====================================================
   ACTION BUTTONS & SEARCH
===================================================== */
    .students-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* SEARCH WRAPPER */
    .search-box-lg {
        width: 260px;
        position: relative;
    }

    /* INPUT */
    .search-box-lg input {
        height: 42px;
        font-size: 14px;
        padding-left: 14px;
        padding-right: 42px;
        /* space for icon */
        border-radius: 22px;
    }

    /* SEARCH ICON */
    .search-box-lg .search-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #999;
        pointer-events: none;
        /* important */
    }


    .icon-btn {
        border: none;
        background: #f5af00;
        color: #fff;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        font-size: 18px;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* =====================================================
   STUDENTS TABLE
===================================================== */
    .students-table thead {
        background: #fff6e5;
    }

    .students-table th {
        font-size: 13px;
        font-weight: 700;
        color: #777;
    }

    .students-table th:first-child,
    .students-table td:first-child {
        width: 36px;
        text-align: center;
    }

    .student-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* =====================================================
   PASS / FAIL INDICATOR
===================================================== */
    .result-box {
        width: 38px;
        height: 37px;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 11px;
        font-weight: 600;
        color: #ffffff;

        border-radius: 5px;
        text-transform: capitalize;
    }

    .result-box.pass {
        background-color: #3cb371;
    }

    .result-box.fail {
        background-color: #e04b4b;
    }

    /* Whole recess row on one line, centered vertically */
    .recess-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    /* Smaller pill for time (narrower than Start/End) */
    .recess-from,
    .recess-to {
        width: 110px;
        /* smaller tab */
        height: 30px;
        /* slightly shorter */
        border-radius: 999px;
        border: 1px solid #e0e0e0;
        background-color: #f5f5f5;
        font-size: 12px;
        /* a bit smaller text */
        padding: 3px 10px;
    }

    /* keep browser clock icon but subtle */
    .recess-from::-webkit-calendar-picker-indicator,
    .recess-to::-webkit-calendar-picker-indicator {
        opacity: 0.6;
    }

    /* "Recess" text in the middle, like image‑2 */
    .recess-label {
        font-size: 13px;
        color: #555;
    }

    /* Small circular X button right after the second time pill */
    .removeRecessBtn {
        width: 22px;
        height: 22px;
        padding: 0;
        border-radius: 50%;
        border: 1px solid #e0e0e0;
        background-color: #f5f5f5;
        font-size: 12px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }



    .timetable-wrapper {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
    }

    .timetable-scroll {
        width: 100%;
        overflow-x: hidden;
    }

    .timetable-grid {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    /* Rows */
    /* .tt-row {
        display: grid;
        grid-template-columns: 110px repeat(auto-fit, minmax(75px, 1fr));
        gap: 4px;
    } */

    .tt-row {
        display: grid;
        grid-auto-flow: column;
        grid-template-columns: 110px repeat(auto-fit, minmax(75px, 1fr));
    }


    /* Cells */
    .tt-cell {
        height: 38px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* Header */
    .tt-head {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 20;
    }

    /* Day column */
    .day {
        position: sticky;
        left: 0;
        z-index: 25;
        background: #7f9fa9;
        color: #fff;
        font-weight: 600;
    }

    /* Time header */
    .time-head {
        background: #f5f7f9;
        color: #666;
        font-size: 11px;
    }

    .day-time-head {
        position: sticky;
        left: 0;
        z-index: 30;
        height: 38px;
        display: grid;
        /* Changed from flex */
        grid-template-columns: 1fr 1fr;
        /* Equal split */
        padding: 0;
        border-radius: 6px;
        overflow: hidden;
        font-weight: 600;
        width: 110px;
        /* Exact day column width */
        justify-self: start;
        /* Aligns perfectly */
    }

    /* LUNCH BREAK - DISTINCT ORANGE */
    .break-head {
        background: #f4c430 !important;
        color: #3b3b3b !important;
        font-weight: 700 !important;
    }

    /* ===== VERTICAL MERGED BREAK COLUMN ===== */
    .break-vertical {
        background: #fff3cd;
        color: #856404;
        font-weight: 700;
        font-size: 12px;

        display: flex;
        align-items: center;
        justify-content: center;

        writing-mode: vertical-rl;
        transform: rotate(180deg);

        border-radius: 8px;
    }

    /* Header style */
    .break-head {
        background: #f4c430 !important;
        color: #3b3b3b !important;
        font-weight: 700;
    }


    .break-cell {
        background: #fff3cd !important;
        color: #856404 !important;
        font-weight: 700;
        font-size: 10px;
    }

    /* Subjects */
    .subject {
        background: #7f8f96;
        color: #fff;
    }

    /* Edit mode */
    .timetable-wrapper.edit-mode .subject {
        cursor: pointer;
        outline: 2px dashed rgba(255, 193, 7, 0.6);
    }

    /* ================= SUBJECT MODAL ================= */

    /* ================= SUBJECT MODAL ================= */

    .subject-dialog {
        max-width: 960px;
    }

    .subject-modal {
        border-radius: 24px;
        border: none;
        background: #fff;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Header */
    .subject-modal-header {
        border-bottom: 1px solid #e9ecef;
    }

    .subject-modal-header .modal-title {
        font-size: 20px;
        font-weight: 600;
    }

    /* Search */
    .subject-search-input {
        border-radius: 20px;
        height: 34px;
        font-size: 12px;
    }

    /* Subject grid */
    .subject-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* ================= SUBJECT PILL ================= */

    .subject-item {
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;

        background: transparent;
        border: 1px solid #6c757d;
        color: #212529;

        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    /* Hover → ONLY scale */
    .subject-item:hover {
        transform: scale(1.08);
    }

    /* Selected subject */
    .subject-item.selected {
        background: #f4b000;
        border-color: #f4b000;
        color: #ffffff;
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .subject-modal-body {
            padding: 16px;
        }
    }


    .transfer-flow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .transfer-box {
        flex: 1;
        padding: 14px;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .from-box {
        border-left: 4px solid #6c757d;
    }

    .to-box {
        border-left: 4px solid #dc3545;
    }

    .transfer-value {
        font-size: 15px;
    }

    .transfer-arrow {
        font-size: 32px;
        color: #dc3545;
        margin-top: 18px;
    }

    .transfer-modal .modal-body {
        padding: 20px;
    }
</style>

<?php $this->load->view('partials/timetable_settings_modal'); ?>