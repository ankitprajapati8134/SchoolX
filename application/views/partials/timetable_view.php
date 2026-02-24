<div class="timetable-wrapper">
    <!-- SCROLL CONTAINER (responsive) -->
    <div class="timetable-scroll">
        <!-- TIMETABLE GRID -->
        <div class="timetable-grid" id="timetableGrid">
            <!-- header + rows injected here -->
        </div>
    </div>

    <!-- FOOTER - SMALL BUTTONS -->
    <div class="timetable-footer">
        <button class="btn btn-warning btn-sm px-3 py-1 mr-2" id="editTimetableBtn">
            Edit
        </button>

        <div id="timetableEditActions" class="hidden">
            <button class="btn btn-outline-secondary btn-sm px-3 py-1 mr-2" id="cancelTimetableEdit">
                Cancel
            </button>
            <button class="btn btn-success btn-sm px-3 py-1" id="saveTimetableEdit">
                Save
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectSelectModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered subject-dialog">
        <div class="modal-content subject-modal">

            <!-- HEADER -->
            <div class="modal-header subject-modal-header">

                <button type="button"
                    class="close subject-close"
                    data-dismiss="modal">&times;</button>

                <h4 class="modal-title">Subjects</h4>

                <!-- SEARCH BELOW TITLE -->
                <div class="subject-search-wrap" style="margin-top:12px;">
                    <input type="text"
                        class="form-control subject-search-input"
                        placeholder="Search subjects"
                        id="subjectSearch">
                    <i class="fa fa-search subject-search-icon"></i>
                </div>

            </div>

            <!-- BODY -->
            <div class="modal-body subject-modal-body">

                <!-- CLASS TITLE -->
                <h3 class="subject-section-title" id="classSubjectTitle"></h3>

                <!-- CLASS SUBJECTS -->
                <div class="subject-grid class-subjects"></div>

                <!-- ALL SUBJECTS -->
                <h3 class="subject-section-title mt-3">
    All Subjects
    <small class="text-muted" id="allSubjectCount"></small>
</h3>

                <div class="subject-grid all-subjects"></div>

            </div>

        </div>
    </div>
</div>






<style>
    /* =====================================================
   TIMETABLE WRAPPER
===================================================== */
    .timetable-wrapper {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
    }

    /* Scroll container */
    .timetable-scroll {
        width: 100%;
        overflow-x: hidden;
    }

    /* =====================================================
   TIMETABLE GRID (ROW-BASED, SAFE)
===================================================== */
    .timetable-grid {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    /* One row = day + periods */
    .tt-row {
        display: grid;
        grid-template-columns: 110px repeat(auto-fit, minmax(120px, 1fr));
        gap: 6px;
    }

    /* =====================================================
   CELLS
===================================================== */
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

    /* =====================================================
   HEADER ROW
===================================================== */
    .tt-head {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #ffffff;
    }

    /* Day | Time cell */
    .day-time-head {
        height: 38px;
        /* EXACT match with .tt-cell */
        min-height: 38px;
    }

    /* Ensure internal spans fill full height */
    .day-time-head span {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-label {
        background: #7f9fa9;
        color: #fff;
    }

    .time-label {
        background: #e9ecef;
        color: #666;
    }

    /* Time header cells */
    .time-head {
        background: #f5f7f9;
        color: #666;
        font-size: 11px;
        font-weight: 500;
    }

    /* =====================================================
   DAY COLUMN
===================================================== */
    .day {
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
    }

    /* Edit mode */
    .timetable-wrapper.edit-mode .subject {
        cursor: pointer;
        outline: 2px dashed rgba(255, 193, 7, 0.6);
    }

    .timetable-wrapper.edit-mode .subject:hover {
        background: #6f8289;
    }

    /* =====================================================
   BREAK (STANDARD COLUMN – NOT MERGED)
===================================================== */
    .break-head {
        background: #f4c430 !important;
        color: #3b3b3b !important;
        font-weight: 700;
    }

    .break-cell {
        background: #fff3cd !important;
        color: #856404 !important;
        font-weight: 700;
        font-size: 11px;
    }

    /* =====================================================
   FOOTER BUTTONS
===================================================== */
    .timetable-footer {
        margin-top: 24px;
        text-align: center;
    }

    .timetable-footer .btn {
        font-size: 12px;
        line-height: 1.4;
    }

    /* =====================================================
   RESPONSIVE
===================================================== */
    @media (max-width: 768px) {
        .timetable-wrapper {
            padding: 16px;
        }

        .tt-cell {
            height: 36px;
            font-size: 10px;
        }

        .time-head {
            font-size: 9px;
        }
    }

    /* =====================================================
   SUBJECT MODAL
===================================================== */
    .subject-dialog {
        max-width: 960px;
    }

    .subject-modal {
        border-radius: 24px;
        border: none;
        background: #ffffff;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Header */
    .subject-modal-header {
        border-bottom: 1px solid #e9ecef;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .subject-modal-header .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
    }

    /* Search */
    .subject-search-wrap {
        position: relative;
        width: 100%;
        margin-top: 12px;
    }

    .subject-search-input {
        width: 100%;
        height: 34px;
        border-radius: 20px;
        font-size: 12px;
        padding: 0 32px 0 14px;
        border: 1px solid #e0e4ea;
    }

    .subject-search-input:focus {
        border-color: #f4b000;
        box-shadow: 0 0 0 0.15rem rgba(244, 176, 0, 0.25);
    }

    .subject-search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
    }

    /* Close */
    .subject-close {
        position: absolute;
        right: 20px;
        top: 16px;
        font-size: 26px;
        color: #c0c4cc;
    }

    .subject-close:hover {
        color: #999;
    }

    /* Body */
    .subject-modal-body {
        padding: 16px 32px 28px;
    }

    /* Subject grids */
    .subject-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    

   

    .subject-item:hover {
        background: #e9ecef;
    }

    .subject-item.selected {
        background: #f4b000;
        border-color: #f4b000;
        color: #fff;
        font-weight: 600;
    }

    /* Responsive modal */
    @media (max-width: 576px) {
        .subject-modal-body {
            padding: 16px;
        }
    }
</style>
