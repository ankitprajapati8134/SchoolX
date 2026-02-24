<div class="content-wrapper">
    <section class="content">

        <!-- HEADER -->
        <div class="box box-default page-header-modern">
            <div class="box-body">
                <div class="row align-items-center">

                    <div class="col-sm-8">
                        <div class="header-left d-flex align-items-center">
                            <i class="fa fa-chalkboard header-icon mr-2"></i>
                            <div class="title-wrap">
                                <h2 class="page-title mb-0">Class Management</h2>
                                <p class="page-subtitle mb-0">Manage all Classes and Sections</p>
                            </div>
                        </div>
                    </div>

                    <!-- SELECT CLASS DROPDOWN -->
                    <div class="col-sm-4 text-right">
                        <div class="class-dropdown-wrapper">
                            <button
                                type="button"
                                class="btn btn-warning btn-sm btn-select-class shadow-sm">
                                <span class="btn-text">Add New Class</span>
                                <span class="dropdown-icon">
                                    <i class="fa fa-chevron-down"></i>
                                </span>
                            </button>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- BODY -->
        <div class="page-body">
            <!-- IMPORTANT: grid is handled by Bootstrap columns injected inside -->
            <div id="classCardsContainer" class="class-grid"></div>
        </div>

    </section>
</div>





<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {

        /* =============================
           LOAD CLASS GRID ON PAGE LOAD
        ============================= */
        loadClassGrid();

        function loadClassGrid() {
            $.ajax({
                url: '<?= base_url("classes/fetch_classes_grid") ?>',
                type: 'POST',
                dataType: 'json',
                success: function(classes) {
                    console.log('Class grid response:', classes);
                    renderClassCards(classes);
                },
                error: function(xhr) {
                    console.error('Class grid error:', xhr.responseText);
                }
            });
        }

        /* =============================
           RENDER CLASS CARDS (BOOTSTRAP GRID)
        ============================= */
        function renderClassCards(classes) {
            const $container = $('#classCardsContainer');
            $container.empty();

            if (!Array.isArray(classes) || classes.length === 0) return;

            classes.forEach(cls => {
                $container.append(`
                <div class="class-grid-item">
                    <div class="card class-card-theme shadow-sm"
                        data-class="${cls.key}"
                        role="button">
                        <div class="card-body d-flex flex-column justify-content-center text-center p-3">
                            <i class="fa fa-chalkboard fa-lg mb-1"></i>
                            <h5 class="mb-0">${cls.label}</h5>
                        </div>
                    </div>
                </div>
                `);
            });
        }




        /* =============================
           CLASS CARD CLICK
        ============================= */
        $(document).on('click', '.class-card-theme', function() {
            const classKey = $(this).data('class'); // "Class 8th"

            // Extract slug → "8th"
            // const slug = classKey.replace(/^Class\s+/i, '');
            const slug = classKey
                .replace(/^Class\s+/i, '')
                .trim();


            window.location.href =
                "<?= base_url('classes/view/') ?>" + encodeURIComponent(slug);
        });



        /* =====================================================
           SELECT CLASS DROPDOWN (UNCHANGED BEHAVIOR, CLEANED)
        ===================================================== */

        let classCache = null;

        function fetchClasses(callback) {
            $.ajax({
                url: '<?= base_url("classes/get_class_details") ?>',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    classCache = data || [];
                    if (callback) callback();
                }
            });
        }

        $('.btn-select-class').on('click', function(e) {
            e.stopPropagation();

            const $menu = $('.class-dropdown-menu');
            if ($menu.length) {
                $menu.remove();
                return;
            }

            if (classCache) {
                renderDropdown();
            } else {
                fetchClasses(renderDropdown);
            }
        });

        function renderDropdown() {
            if (!Array.isArray(classCache) || classCache.length === 0) return;

            $('.class-dropdown-menu').remove(); // safety

            const $btn = $('.btn-select-class');
            const btnWidth = $btn.outerWidth(); // 🔑 exact width

            let html = `<div class="class-dropdown-menu" style="width:${btnWidth}px">`;

            classCache.forEach(cls => {
                html += `
            <div class="class-option" data-name="${cls.label}">
                ${cls.label}
            </div>
                `;
            });

            html += `</div>`;

            $('.class-dropdown-wrapper').append(html);
        }


        $(document).on('click', '.class-option', function(e) {
            e.stopPropagation();

            const classLabel = $(this).data('name'); // "Class 4th"

            // Update button text
            $('.btn-text').text(classLabel);
            $('.class-dropdown-menu').remove();

            // 🔐 Ensure class exists in Firebase
            $.post(
                '<?= base_url("classes/ensure_class_exists") ?>', {
                    class_name: classLabel
                },
                function(res) {

                    if (res.status !== 'success') {
                        alert(res.message || 'Unable to create class');
                        return;
                    }

                    // 🔁 Reload class grid
                    loadClassGrid();

                    // ✅ Redirect to class profile page
                    // const slug = classLabel.replace(/^Class\s+/i, '');

                    const slug = classLabel
                        .replace(/^Class\s+/i, '')
                        .trim();


                    window.location.href =
                        "<?= base_url('classes/view/') ?>" +
                        encodeURIComponent(slug);
                },
                'json'
            );
        });


        $(document).on('click', function() {
            $('.class-dropdown-menu').remove();
        });

    });
</script>



<style>
    /* =====================================================
   SELECT CLASS BUTTON
    ===================================================== */
    .btn-select-class {
        background-color: #f5af00 !important;
        border-radius: 10px;
        padding: 6px 10px;
        font-size: 13px;

        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    /* Circular arrow */
    .btn-select-class .dropdown-icon {
        width: 22px;
        height: 22px;
        min-width: 22px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);

        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-select-class .dropdown-icon i {
        font-size: 10px;
        line-height: 1;
    }


    /* =====================================================
   SELECT CLASS DROPDOWN
===================================================== */
    .class-dropdown-wrapper {
        position: relative;
    }

    .class-dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;

        background: #fff;
        border-radius: 8px;
        padding: 4px 0;
        z-index: 1000;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);

        max-height: 220px;
        overflow-y: auto;
    }

    .class-option {
        padding: 8px 14px;
        font-size: 13px;
        cursor: pointer;
        white-space: nowrap;
    }

    .class-option:hover {
        background: rgba(245, 175, 0, 0.15);
    }


    /* =====================================================
   CLASS GRID LAYOUT (FLEX-BASED)
===================================================== */
    .page-body {
        padding: 20px 0;
    }

    /* Grid container */
    .class-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 24px 32px;
        /* vertical | horizontal */
    }

    /* Grid item wrapper */
    .class-grid-item {
        flex: 0 0 auto;
    }

    .section-card {
        width: 260px;
        height: 340px;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .section-footer {
        background: #f5af00;
        color: #fff;
        text-align: center;
        padding: 10px;
        font-size: 14px;
    }



    /* =====================================================
    CLASS CARD
    ===================================================== */
    .class-card-theme {
        background: linear-gradient(135deg, #f5af00, #ffcc4d);
        border-radius: 16px;
        color: #fff;

        width: 200px;
        height: 120px;

        display: flex;
        align-items: center;
        justify-content: center;

        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .class-card-theme:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.22);
    }
</style>