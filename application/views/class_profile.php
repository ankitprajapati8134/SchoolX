<div class="content-wrapper">
    <section class="content section-management-page">
        <!-- HEADER - EXACT MATCH TO IMAGE 1 -->
        <div class="section-header">
            <div class="section-header-top">
                <h2 class="mb-0">Section Management</h2>
                <button class="btn btn-warning btn-sm selected-class-btn" disabled>
                    <?= htmlspecialchars($class_name) ?>
                    <i class="fa fa-chevron-down ml-1"></i>
                </button>
            </div>
            <div class="section-header-center">
                <button class="btn btn-warning btn-sm add-section-btn" id="addSectionBtn">
                    <span class="plus-circle">+</span> Add Section
                </button>
            </div>
        </div>

        <!-- PERFECT GRID FROM IMAGE 1 -->
        <div id="sectionContainer" class="class-grid">
            <!-- Sections injected here -->
        </div>

        <!-- ADD SECTION MODAL -->
        <div class="modal fade" id="addSectionModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content modern-modal">

                    <!-- Header -->
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">
                            <i class="fa fa-layer-group text-warning mr-1"></i> Add Section
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Section Name</label>
                            <input type="text" id="sectionNameInput" class="form-control modern-input" readonly>
                        </div>

                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Maximum Strength</label>
                            <input type="number" id="sectionStrengthInput" class="form-control modern-input" placeholder="Enter maximum capacity" min="1">
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-light btn-md rounded-pill shadow-sm" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-warning btn-md rounded-pill shadow-sm px-4" id="saveSectionBtn">
                            <i class="fa fa-save mr-1"></i> Save Section
                        </button>
                    </div>

                </div>
            </div>
        </div>


    </section>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ALL YOUR ORIGINAL JS EXACTLY PRESERVED (just added Bootstrap for modal)
    const CLASS_NAME = <?= json_encode($class_name) ?>;

    $(document).ready(function() {
        if (!CLASS_NAME) return;
        fetchSections(CLASS_NAME);
    });

    // fetchSections, renderSections, modals, etc. - EXACTLY YOUR ORIGINAL CODE
    function fetchSections(className) {
        $.ajax({
            url: '<?= base_url("classes/fetch_class_sections") ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                class_name: className
            },
            success: function(sections) {
                renderSections(sections);
            },
            error: function() {
                $('#sectionContainer').html('<p class="text-danger">Failed to load sections.</p>');
            }
        });
    }

    function renderSections(sections) {
        const $container = $('#sectionContainer');
        $container.empty();

        if (!Array.isArray(sections) || sections.length === 0) {
            $container.html('<p class="text-muted">No sections created yet.</p>');
            return;
        }

        sections.forEach(section => {
            let studentHtml = '';
            if (section.students && section.students.length > 0) {
                section.students.slice(0, 8).forEach(stu => { // Show max 8
                    studentHtml += `<div class="student-name">${stu.name}</div>`;
                });
                if (section.students.length > 8) {
                    studentHtml += `<div class="more-students">+${section.students.length - 8} more</div>`;
                }
            } else {
                studentHtml = `<div class="text-muted small">No students yet</div>`;
            }

            $container.append(`
            <div class="class-grid-item">
                <div class="section-title">${section.name}</div>
                <div class="card section-card shadow-sm" data-section="${section.name}">
                    <div class="section-students">${studentHtml}</div>
                    <div class="section-footer">
                        Total Strength ${section.strength} / ${section.max_strength}
                    </div>
                </div>
            </div>
        `);
        });
    }

    // ALL YOUR OTHER FUNCTIONS EXACTLY THE SAME (addSectionBtn, getNextSectionLetter, saveSectionBtn, appendSectionCard, click handler)
    $('#addSectionBtn').on('click', function() {
        const nextSection = getNextSectionLetter();
        $('#sectionNameInput').val('Section ' + nextSection);
        $('#sectionStrengthInput').val('');
        $('#addSectionModal').modal('show');
    });

    function getNextSectionLetter() {
        const letters = [];
        $('.section-title').each(function() {
            const text = $(this).text().trim();
            const match = text.match(/Section\s+([A-Z])/i);
            if (match) letters.push(match[1].toUpperCase());
        });
        if (letters.length === 0) return 'A';
        letters.sort();
        const last = letters[letters.length - 1];
        return String.fromCharCode(last.charCodeAt(0) + 1);
    }

    $('#saveSectionBtn').on('click', function() {
        const sectionName = $('#sectionNameInput').val().trim();
        const maxStrength = $('#sectionStrengthInput').val().trim();
        if (!maxStrength || maxStrength <= 0) {
            alert('Please enter valid maximum strength');
            return;
        }
        $.ajax({
            url: '<?= base_url("classes/add_section") ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                class_name: CLASS_NAME,
                section_name: sectionName,
                max_strength: maxStrength
            },
            success: function(res) {
                if (res.status === 'success') {
                    $('#addSectionModal').modal('hide');
                    appendSectionCard({
                        name: res.section,
                        strength: 0,
                        max_strength: res.max_strength
                    });
                } else {
                    alert(res.message || 'Failed to add section');
                }
            }
        });
    });

    function appendSectionCard(section) {
        const $card = $(`
        <div class="class-grid-item" style="display:none">
            <div class="section-title">${section.name}</div>
            <div class="card section-card shadow-sm" data-section="${section.name}">
                <div class="section-students">
                    <div class="text-muted small">No students yet</div>
                </div>
                <div class="section-footer">Total Strength ${section.strength} / ${section.max_strength}</div>
            </div>
        </div>
    `);
        $('#sectionContainer').append($card);
        $card.fadeIn(300);
    }

    $(document).on('click', '.section-card', function() {
        const sectionName = $(this).data('section');
        if (!sectionName) return;
        const classSlug = CLASS_NAME.replace('Class ', '');
        const sectionSlug = sectionName.replace('Section ', '');
        window.location.href = `<?= base_url('classes/section_students/') ?>${classSlug}/${sectionSlug}`;
    });
</script>

<style>
    /* =====================================================
   SECTION PAGE LAYOUT
===================================================== */
    .section-management-page {
        background: #f8f9fa;
        padding: 28px;
        min-height: 100vh;
    }

    /* HEADER */
    .section-header {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto 48px;
    }

    .section-header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 14px;
        margin-bottom: 22px;
    }

    .section-header-top h2 {
        font-size: 26px;
        font-weight: 700;
        color: #1e293b;
    }

    .section-header-center {
        display: flex;
        justify-content: center;
        padding: 0 14px;
    }

    /* BUTTONS */
    .selected-class-btn,
    .add-section-btn {
        border-radius: 24px;
        padding: 10px 20px;
        font-weight: 600;
        min-width: 180px;
        font-size: 14px;
        box-shadow: 0 6px 18px rgba(245, 175, 0, 0.30);
    }

    /* GRID */
    .class-grid {
        display: grid !important;
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 28px 64px;
        gap: 40px 48px;
        justify-content: center;
    }

    /* RESPONSIVE GRID */
    @media (max-width: 575px) {
        .section-management-page {
            padding: 20px;
        }

        .class-grid {
            grid-template-columns: 100%;
            gap: 32px;
            padding: 0 12px 48px;
        }
    }

    @media (min-width: 576px) and (max-width: 991px) {
        .class-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 36px 40px;
            padding: 0 20px 60px;
        }
    }

    @media (min-width: 992px) {
        .class-grid {
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        }
    }

    /* GRID ITEM */
    .class-grid-item {
        display: flex;
        flex-direction: column;
        max-width: 360px;
        min-height: 500px;
    }

    /* TITLE */
    .section-title {
        font-size: 19px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 26px;
        text-align: center;
    }

    /* CARD */
    .section-card {
        flex: 1;
        border-radius: 24px;
        background: #ffffff;
        position: relative;
        transition: 0.3s ease;
        box-shadow: 0 14px 40px rgba(0, 0, 0, 0.10);
        border: 1px solid #f0f9ff;
        min-height: 440px;
    }

    .section-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 26px 60px rgba(0, 0, 0, 0.16);
    }

    /* STUDENT LIST */
    .section-students {
        padding: 32px 26px 26px;
        height: calc(100% - 96px);
        overflow-y: auto;
        font-size: 13px;
        color: #64748b;
    }

    .student-name {
        padding: 7px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .more-students {
        color: #f59e0b;
        font-weight: 600;
        padding: 10px 0;
        font-size: 13.5px;
    }

    /* FOOTER */
    .section-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        text-align: center;
        padding: 12px;
        font-size: 15px;
        font-weight: 700;
        border-radius: 0 0 24px 24px;
    }

    /* SCROLLBAR */
    .section-students::-webkit-scrollbar {
        width: 4px;
    }

    .section-students::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.35);
        border-radius: 3px;
    }

    /* =====================================================
   PROFESSIONAL MODAL DESIGN (SMALLER)
===================================================== */
    .modern-modal {
        border-radius: 16px;
        padding: 10px 6px 6px;
        border: none;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.15);
    }

    /* HEADER */
    .modern-modal .modal-header .modal-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
    }

    .modern-modal .close {
        font-size: 22px;
        opacity: .5;
    }

    .modern-modal .close:hover {
        opacity: .9;
    }

    /* INPUT DESIGN */
    .modern-input {
        height: 44px;
        border-radius: 10px;
        padding: 8px 12px;
        border: 1px solid #dbeafe;
        background: #f8fafc;
        font-size: 14px;
        transition: .25s ease;
    }

    .modern-input:focus {
        border-color: #f59e0b;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.20);
    }

    /* MODAL BUTTONS */
    .modal-footer .btn {
        font-weight: 600;
        border-radius: 32px !important;
        font-size: 14px;
    }

    /* BUTTON COLORS */
    .btn-warning {
        color: #fff;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        border: none;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .btn-light {
        background: #f1f5f9;
        border: none;
    }

    .btn-light:hover {
        background: #e2e8f0;
    }
</style>