$(document).ready(function () {

    /* ============================================================
               1. CORE VARIABLES — SHARED ACROSS ALL JS
           ============================================================ */
    const $staffTable = $('.staff_table');
    const $form = $('#formdata');
    const $holidayCheckbox = $('input[name="holiday"]');
    const $dateDisplayContainer = $('#dateDisplayContainer');
    const $dateDisplay = $('#currentDate');
    const $hiddenDatePicker = $('#hiddenDatePicker');
    const $selectedDate = $('#selectedDate');
    const $displayDate = $('#displayDate');
    const $staffListContainer = $('#staffListContainer');
    const $multiBar = $('#multiSelectBar');
    const $avatars = $('#selectedStaffAvatars');
    const $count = $('#selectedCount');
    const currentMonthEl = document.querySelector(".current-month");
    const prevBtn = document.querySelector(".prev-month");
    const nextBtn = document.querySelector(".next-month");
    const tbody = document.querySelector("#staff_month_attendance_data");
    const fetchUrl = window.staffAttendanceDataUrl;
    const search = document.getElementById('search');
    let attendanceState = {};  // Stores temporary attendance input before saving
    let isHoliday = false;
    let currentDateObj = new Date();

    /* ============================================================
       2. DATE HELPERS — UTILITY FUNCTIONS FOR DATE FORMAT
    ============================================================ */
    function getDayWithSuffix(day) {
        if (day > 3 && day < 21) return day + "th";
        switch (day % 10) {
            case 1: return day + "st";
            case 2: return day + "nd";
            case 3: return day + "rd";
            default: return day + "th";
        }
    }

    function formatDisplayDate(date) {
        const month = date.toLocaleString('en-US', { month: 'long' });
        const day = getDayWithSuffix(date.getDate());
        const year = date.getFullYear();
        return `${month} ${day}, ${year}`;
    }

    function formatBackendDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function parseBackendDate(dateStr) {
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }
        return new Date();
    }

    /* ============================================================
       3. DATEPICKER LOGIC
    ============================================================ */
    $hiddenDatePicker.datepicker({
        todayHighlight: true,
        autoclose: true,
        format: "yyyy-mm-dd",
        orientation: "bottom auto",
        endDate: new Date(),
        rtl: isRTL()
    }).on('changeDate', function (e) {
        if (!e.date) return;
        currentDateObj = e.date;
        const displayFormatted = formatDisplayDate(e.date);
        const backendFormatted = formatBackendDate(e.date);
        $dateDisplay.text(displayFormatted);
        $selectedDate.val(backendFormatted);
        $displayDate.val(displayFormatted);
        refreshAttendanceData(backendFormatted);
    });

    // UI interactions for datepicker
    $dateDisplayContainer.on('click keydown', function (e) {
        if (e.type === 'click' || e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $hiddenDatePicker.datepicker('show');
        }
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#dateDisplayContainer, .datepicker').length)
            $hiddenDatePicker.datepicker('hide');
    });

    // Initialize default date
    const initialDate = $selectedDate.val();
    if (initialDate) {
        const parsed = parseBackendDate(initialDate);
        if (!isNaN(parsed.getTime())) {
            currentDateObj = parsed;
            $hiddenDatePicker.datepicker('setDate', parsed);
        }
    }

    /* ============================================================
       4. FETCH ATTENDANCE DATA (AJAX)
    ============================================================ */
    function refreshAttendanceData(dateString) {
        if (!dateString) return;
        attendanceState = {};
        $staffTable.bootstrapTable('refresh');
        $.ajax({
            url: fetchUrl,
            type: "GET",
            data: { date: dateString, mode: 'daily' },
            success: function (response) {
                if (response == 3) {
                    // Marked as holiday
                    isHoliday = true;
                    $holidayCheckbox.prop('checked', true).val(3);
                    $staffTable.slideUp(400);
                } else {
                    // Regular working day
                    isHoliday = false;
                    $holidayCheckbox.prop('checked', false).val(0);
                    $staffTable.slideDown(400);
                }
            },
            error: function () {
                isHoliday = false;
                $holidayCheckbox.prop('checked', false).val(0);
                $staffTable.slideDown(400);
                Swal.fire({
                    icon: 'error',
                    title: window.trans['Error'],
                    text: window.trans['Failed to load attendance data. Please try again.']
                });
            }
        });
    }

    /* ============================================================
       5. HOLIDAY TOGGLE HANDLER
    ============================================================ */
    $holidayCheckbox.on('click', function (e) {
        const selectedDate = $selectedDate.val();
        const checkBox = this;
        const wasChecked = checkBox.checked;

        if (!selectedDate) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: window.trans['Warning'],
                text: window.trans['Please select a date first']
            });
            return;
        }

        Swal.fire({
            title: window.trans['are_you_sure'],
            text: wasChecked
                ? window.trans['Mark this date as a holiday?']
                : window.trans['Remove holiday status from this date?'],
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: window.trans['Yes'],
            cancelButtonText: window.trans['Cancel']
        }).then((result) => {
            if (result.isConfirmed) {
                isHoliday = wasChecked;
                $holidayCheckbox.val(wasChecked ? 3 : 0);
                if (wasChecked) {
                    $staffTable.slideUp(400);
                    $('.staff_table input[type="checkbox"]').prop('checked', false);
                    $('.staff_table input[type="checkbox"]').trigger('change');
                    $('#saveAttendanceBtn').removeClass('d-none');
                } else {
                    $staffTable.slideDown(400);
                    $('#saveAttendanceBtn').addClass('d-none');
                }
            } else {
                checkBox.checked = !wasChecked;
            }
        });
    });

    /* ============================================================
       6. MODAL UI LOGIC
    ============================================================ */
    // Attendance radio change (Present / Absent / Half-day)
    $(document).on('change', 'input[name="attendance_status"]', function () {
        const value = this.value;
        const halfDaySection = document.getElementById('halfDaySelection');
        const reasonSection = document.querySelector('.reasonSection');
        if (value === 'half_day') {
            halfDaySection.classList.remove('d-none');
            $('input[value="first_half"]').prop('checked', true);
        } else {
            halfDaySection.classList.add('d-none');
            document.querySelectorAll('input[name="half_day_type"]').forEach(el => el.checked = false);
        }
        if (value === 'present') {
            reasonSection.classList.add('d-none');
            document.getElementById('reason').value = '';
        } else {
            reasonSection.classList.remove('d-none');
        }
    });

    // Single staff mark modal
    $(document).on('click', '.mark-btn', function () {

        /* ===============================
           1. EXTRACT DATA
        =============================== */
        const name = $(this).data('name');
        const date = $(this).data('date');
        const staffId = $(this).data('staff-id');
        const attendanceId = $(this).data('attendance-id');

        const attendanceType = $(this).data('attendance-type');   // 0,1,4,5
        const leaveType = $(this).data('leave-type');             // Full | First Half | Second Half | null
        const adminLeave = $(this).data('admin-leave');           // true/false
        const attendanceLeave = $(this).data('attendance-leave'); // true/false
        const reason = $(this).data('reason');

        /* ===============================
           2. RESET MODAL
        =============================== */
        $('#staffName').text(name);
        $('#attendanceDate').text(date);
        $('#staffIds').val(staffId);
        $('#attendanceIds').val(attendanceId);

        $('#reason').val('');
        $('#reason').prop('disabled', false);

        $('input[name="attendance_status"]').prop('checked', false).prop('disabled', false);
        $('input[name="half_day_type"]').prop('checked', false).prop('disabled', false);

        $('#halfDaySelection').addClass('d-none');
        $('.reasonSection').removeClass('d-none');

        /* ===============================
           ✅ CASE 1: ADMIN FULL DAY LEAVE
        =============================== */
        if (adminLeave && leaveType === 'Full') {

            // Fully disable UI
            $('input[name="attendance_status"]').prop('disabled', true);
            $('input[name="half_day_type"]').prop('disabled', true);
            $('#reason').prop('disabled', true);

            $('#markAttendanceModal').modal('show');
            return;
        }

        /* ===============================
           ✅ CASE 2: ADMIN HALF-DAY LEAVE
        =============================== */
        if (adminLeave && (leaveType === 'First Half' || leaveType === 'Second Half')) {

            // Force half-day
            $('input[value="half_day"]').prop('checked', true);
            $('#halfDaySelection').removeClass('d-none');

            // Disable present
            $('input[value="present"]').prop('disabled', true);

            if (leaveType === 'First Half') {
                // First Half admin leave → only Second half selectable
                $('input[value="first_half"]').prop('disabled', true);
                $('input[value="second_half"]').prop('checked', true);
            }

            if (leaveType === 'Second Half') {
                $('input[value="second_half"]').prop('disabled', true);
                $('input[value="first_half"]').prop('checked', true);
            }

            // No reason for admin leave
            $('.reasonSection').addClass('d-none');
            if (attendanceType !== undefined && attendanceType !== null && attendanceType !== '') {
                switch (parseInt(attendanceType)) {
                    case 0:
                        $('input[value="absent"]').prop('checked', true).trigger('change');
                        break;
                }
                $('.reasonSection').removeClass('d-none');
                if (reason && reason.trim() !== '' && reason !== 'undefined') {
                    $('#reason').val(reason);
                }
            }


            $('#markAttendanceModal').modal('show');
            return;
        }

        /* ===============================
           ✅ CASE 3: ATTENDANCE-CREATED LEAVE (Always editable)
        =============================== */
        if (attendanceLeave) {

            // Only disable opposite half, not the entire attendance
            if (leaveType === 'First Half') {
                $('input[value="second_half"]').prop('checked', true);
            }

            if (leaveType === 'Second Half') {
                $('input[value="first_half"]').prop('checked', true);
            }
        }

        /* ===============================
           ✅ NORMAL PREFILL LOGIC
        =============================== */
        if (attendanceType !== 'undefined' && attendanceType !== null && attendanceType !== '') {

            switch (parseInt(attendanceType)) {

                case 1:
                    $('input[value="present"]').prop('checked', true).trigger('change');
                    break;

                case 0:
                    $('input[value="absent"]').prop('checked', true).trigger('change');
                    break;

                case 4:
                    $('input[value="half_day"]').prop('checked', true).trigger('change');
                    $('#halfDaySelection').removeClass('d-none');
                    $('input[value="first_half"]').prop('checked', true);
                    break;

                case 5:
                    $('input[value="half_day"]').prop('checked', true).trigger('change');
                    $('#halfDaySelection').removeClass('d-none');
                    $('input[value="second_half"]').prop('checked', true);
                    break;
            }
        }
        else {
            // DEFAULT TO PRESENT (NOW FIXED)
            $('input[value="present"]').prop('checked', true).trigger('change');
        }

        /* ===============================
           ✅ PREFILL REASON
        =============================== */
        if (reason && reason.trim() !== '' && reason !== 'undefined') {
            $('#reason').val(reason);
        }

        $('#markAttendanceModal').modal('show');
    });

    // Save modal state on close
    $('#markAttendanceModal').on('hide.bs.modal', function () {
        const name = $('#staffName').text();
        const date = $('#attendanceDate').text();
        const key = name + '_' + date;
        const status = $('input[name="attendance_status"]:checked').val() || null;
        const half_day_type = $('input[name="half_day_type"]:checked').val() || null;
        const reason = $('#reason').val().trim();
        attendanceState[key] = { status, half_day_type, reason };
    });

    /* ============================================================
       7. MULTI-SELECT BAR LOGIC
    ============================================================ */
    $(document).on('change', '.staff_table input[type="checkbox"]', function () {
        const isSelectAll = $(this).attr('id') === 'btSelectAll';
        const $allBoxes = $('.staff_table tbody input[type="checkbox"]');
        if (isSelectAll) {
            const checked = $(this).prop('checked');
            $allBoxes.prop('checked', checked).trigger('change');
            return;
        }

        $(this).closest('tr').toggleClass('selected', this.checked);
        const checked = $('.staff_table tbody input[type="checkbox"]:checked');

        if (checked.length > 0) {
            $multiBar.removeClass('d-none hide').addClass('show');
            $count.text(`${checked.length} staff member${checked.length > 1 ? 's' : ''} selected`);
            $avatars.empty();

            checked.each(function (index) {
                if (index < 5) {
                    const row = $(this).closest('tr');
                    const img = row.find('img').attr('src');
                    const name = row.find('td').eq(1).text().trim();
                    const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    if (img) $avatars.append(`<img src="${img}" alt="${name}" title="${name}">`);
                    else $avatars.append(`<div class="avatar-initials" title="${name}">${initials}</div>`);
                } else if (index === 5) {
                    $avatars.append(`<div class="avatar-initials">+${checked.length - 5}</div>`);
                }
            });
        } else {
            $multiBar.addClass('hide');
            setTimeout(() => $multiBar.addClass('d-none'), 200);
        }

        $('#btSelectAll').prop('checked', $allBoxes.length === checked.length);
    });

    // Clear selected checkboxes
    $('#clearSelected').on('click', function () {
        $('.staff_table input[type="checkbox"]').prop('checked', false);
        $('.staff_table tr.selected').removeClass('selected');
        $('.staff_table input[type="checkbox"]').trigger('change');
    });

    /* ============================================================
       8. BATCH MARKING (MODAL TRIGGER)
    ============================================================ */
    $('#markSelected').on('click', function () {

        const selected = $('.staff_table tbody input[type="checkbox"]:checked');
        if (selected.length === 0) return;

        let staffIds = [];
        let attendanceIds = [];
        let skipped = 0;

        selected.each(function () {

            const tr = $(this).closest('tr');
            const btn = tr.find('.mark-btn');   // ✅ mark button already contains all needed data

            const adminLeave = btn.data('admin-leave');   // ✅ true/false
            const attendanceLeave = btn.data('attendance-leave'); // if needed later

            // ✅ Skip staff having admin-approved leave
            if (adminLeave === true || adminLeave === "true" || adminLeave === 1) {
                skipped++;
                return;
            }

            const staffId = btn.data('staff-id');
            const attendanceId = btn.data('attendance-id');

            if (staffId) staffIds.push(staffId);
            if (attendanceId) attendanceIds.push(attendanceId);
        });

        // ✅ No remaining staff after filtering — show warning & exit
        if (staffIds.length === 0) {
            showErrorToast(window.trans['all_selected_staff_have_admin_approved_leave']);
            return;
        }

        // ✅ Prepare modal for valid staff
        $('#staffIds').val(staffIds.join(','));
        $('#attendanceIds').val(attendanceIds.join(','));
        $('#multipleStaff').val(1);

        $('#staffName').text(`${staffIds.length} staff member${staffIds.length > 1 ? 's' : ''}`);
        $('#attendanceDate').text($('#currentDate').text());

        // ✅ Bulk mode → only Present/Absent, no half day, no reason
        $('#halfDayOption, .reasonSection').addClass('d-none');

        $('input[name="attendance_status"], input[name="half_day_type"]').prop('checked', false);
        $('input[value="present"]').prop('disabled', false);
        $('input[value="present"]').prop('checked', true).trigger('change');

        $('#markAttendanceModal').modal('show');

        // ✅ Inform user some staff skipped (Optional)
        if (skipped > 0) {
            showWarningToast(`${skipped} ${window.trans['staff_skipped_due_to_leave']}`);
        }
    });

    // Reset modal after close
    $('#markAttendanceModal').on('hidden.bs.modal', function () {
        $('.reasonSection').removeClass('d-none');
        $('#halfDayOption').removeClass('d-none');
    });

    /* ============================================================
       9. INITIAL DATA LOAD — MAIN SCRIPT ONLY
    ============================================================ */
    if ($selectedDate.val()) refreshAttendanceData($selectedDate.val());

    /* ============================================================
       10. DAILY / MONTHLY VIEW TOGGLE
    ============================================================ */
    $('#dailyView, #monthlyView').on('click', function () {
        const isDaily = $(this).attr('id') === 'dailyView';

        // Toggle button styles
        $('#dailyView, #monthlyView').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');

        if (isDaily) {
            // Switch to daily view
            $('.dailyAttendanceSection').removeClass('d-none');
            $('.dailyAttendanceDateSection').removeClass('d-none');
            $('.dailyAttendanceDateSection').addClass('d-flex');
            $('.monthlyAttendanceSection').addClass('d-none');
            $('.monthlyAttendanceDateSection').addClass('d-none');
            $('.monthlyAttendanceDateSection').removeClass('d-flex');
        } else {
            // Switch to monthly view
            $('.dailyAttendanceSection').addClass('d-none');
            $('.dailyAttendanceDateSection').addClass('d-none');
            $('.dailyAttendanceDateSection').removeClass('d-flex');
            $('.monthlyAttendanceSection').removeClass('d-none');
            $('.monthlyAttendanceDateSection').removeClass('d-none');
            $('.monthlyAttendanceDateSection').addClass('d-flex');
            renderMonth();
            // loadMonthlyAttendance();
        }
    });

    /* ============================================================
       11. MONTH NAVIGATION LOGIC
    ============================================================ */

    prevBtn.addEventListener("click", () => {
        currentDateObj.setMonth(currentDateObj.getMonth() - 1);
        renderMonth();
    });

    nextBtn.addEventListener("click", () => {
        currentDateObj.setMonth(currentDateObj.getMonth() + 1);
        renderMonth();
    });

    function renderMonth() {
        const options = { year: "numeric", month: "long" };
        currentMonthEl.textContent = currentDateObj.toLocaleDateString("en-US", options);
        const month = currentDateObj.getMonth() + 1;
        const year = currentDateObj.getFullYear();
        loadAttendanceData(month, year);
    }

    renderMonth();

    /* ============================================================
       12. LOAD MONTHLY ATTENDANCE DATA (AJAX)
    ============================================================ */
    function loadAttendanceData(month, year, searchValue = '') {
        tbody.innerHTML = `<tr><td colspan="32" class="text-center">${window.trans['loading...']}</td></tr>`;

        $.ajax({
            url: fetchUrl,
            type: "GET",
            data: {
                month: month,
                year: year,
                mode: 'monthly',
                search: searchValue
            },
            beforeSend: function () {
                tbody.innerHTML = `<tr><td colspan="32" class="text-center">${window.trans['loading...']}</td></tr>`;
            },
            success: function (data) {
                if (data.message === "No user selected") {
                    tbody.innerHTML = `<tr><td colspan="32" class="text-center">${window.trans['no_users_assigned']}</td></tr>`;
                } else if (data.message === "No attendance records found.") {
                    tbody.innerHTML = `<tr><td colspan="32" class="text-center">${window.trans['no_attendance_records_found']}</td></tr>`;
                } else {
                    renderAttendanceTable(data, month, year);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                tbody.innerHTML = `<tr><td colspan="32" class="text-center text-danger">${window.trans['failed_to_load_data']}</td></tr>`;
            }
        });
    }

    function renderAttendanceTable(data, month, year) {
        tbody.innerHTML = "";

        const staffList = data.staff || [];
        const attendanceList = data.attendance || [];
        const leaves = data.leaves || [];
        const holidays = data.holiday || [];

        const daysInMonth = new Date(year, month, 0).getDate();

        // 1️⃣ If literally *no staff*, show message
        if (staffList.length === 0) {
            tbody.innerHTML = `<tr>
            <td colspan="${daysInMonth + 1}" class="text-center">
                ${window.trans['no_attendance_records_found']}
            </td>
        </tr>`;
            return;
        }

        // ---------------------
        // 2️⃣ BUILD FAST LOOKUP MAPS
        // ---------------------

        // Attendance Map → attendanceMap["staffId_2025-01-05"]
        const attendanceMap = {};
        attendanceList.forEach(a => {
            const key = `${a.staff_id}_${formatDate(a.get_date_original)}`;
            attendanceMap[key] = a;
        });

        // Leave Map → leaveMap["staffId_2025-01-05"]
        const leaveMap = {};
        leaves.forEach(l => {
            if (!l.leave_detail) return;
            l.leave_detail.forEach(d => {
                const key = `${l.user_id}_${formatDate(d.date)}`;
                leaveMap[key] = d;
            });
        });

        // Holiday Map → holidayMap["2025-01-05"]
        const holidayMap = {};
        holidays.forEach(h => {
            holidayMap[formatDate(h.date)] = h;
        });

        // ---------------------
        // 3️⃣ BUILD TABLE ROWS PER STAFF
        // ---------------------

        staffList.forEach(staff => {
            const row = document.createElement("tr");

            const user = staff.user || {};
            const userId = staff.user.id;

            const fullName = user.full_name || `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim();
            const imageSrc = user.image || "/assets/images/default-avatar.png";

            // Staff profile cell
            const userCell = document.createElement("td");
            userCell.innerHTML = `
            <div class="d-flex align-items-center">
                <a data-toggle="lightbox" href="${imageSrc}">
                    <img src="${imageSrc}"
                         class="rounded-circle border"
                         style="width:50px;height:50px;object-fit:cover;"
                         onerror="onErrorImage(event)">
                </a>
                <div class="ms-3">
                    <h6 class="mb-0">${fullName || "-"}</h6>
                </div>
            </div>
        `;
            row.appendChild(userCell);

            // ---------------------
            // 4️⃣ DAY-BY-DAY CELLS
            // ---------------------
            for (let day = 1; day <= daysInMonth; day++) {
                const cell = document.createElement("td");
                cell.classList.add("text-center", "p-1");

                const dateStr = `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                const attendance = attendanceMap[`${userId}_${dateStr}`];
                const leave = leaveMap[`${userId}_${dateStr}`];
                const holiday = holidayMap[dateStr];

                // Priority Logic:
                // 1) Holiday
                // 2) Attendance
                // 3) Leave
                // 4) Empty
                if (holiday) {
                    cell.innerHTML = `<i class="fa fa-circle text-holiday" title="${holiday.title ?? 'Holiday'}"></i>`;

                } else if (attendance) {
                    switch (attendance.type) {
                        case 1:
                            cell.innerHTML = `<i class="fa fa-circle text-present" title="Present"></i>`;
                            break;
                        case 0:
                            cell.innerHTML = `<i class="fa fa-circle text-absent" title="Absent"></i>`;
                            break;
                        case 3:
                            cell.innerHTML = `<i class="fa fa-circle text-holiday" title="Holiday Marked"></i>`;
                            break;
                        case 4:
                        case 5:
                            cell.innerHTML = `<i class="fa fa-circle text-half-day" title="Half Day"></i>`;
                            break;
                        default:
                            cell.innerHTML = `<i class="fa fa-circle text-secondary"></i>`;
                    }

                } else if (leave) {
                    cell.innerHTML = `<i class="fa fa-circle text-leave" title="Leave"></i>`;

                } else {
                    cell.innerHTML = ""; // blank cell
                }

                row.appendChild(cell);
            }

            tbody.appendChild(row);
        });

        // ---------------------
        // 5️⃣ ENABLE TOOLTIP
        // ---------------------
        if (window.bootstrap) {
            document.querySelectorAll("[title]").forEach(el =>
                new bootstrap.Tooltip(el)
            );
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return "";

        // Replace all separators with a single type for consistency
        const clean = dateStr.trim().replace(/[-.]/g, "/");
        const parts = clean.split("/").map(p => p.padStart(2, "0"));

        if (parts.length !== 3) return "";

        // Detect format pattern based on logic (Y always > 31)
        let day, month, year;

        // Case 1: YYYY first (Y/m/d or Y-d-m)
        if (parts[0].length === 4) {
            [year, month, day] = parts;
        }
        // Case 2: YYYY last (d/m/Y or m/d/Y)
        else if (parts[2].length === 4) {
            // Check which part looks like a month (<=12)
            if (Number(parts[0]) > 12 && Number(parts[1]) <= 12) {
                // d/m/Y
                [day, month, year] = parts;
            } else if (Number(parts[1]) > 12 && Number(parts[0]) <= 12) {
                // m/d/Y
                [month, day, year] = parts;
            } else {
                // Ambiguous — default to d/m/Y
                [day, month, year] = parts;
            }
        }
        // Case 3: Short year (like d-m-y)
        else {
            [day, month, year] = parts;
            year = "20" + year; // assumes 20xx
        }

        return `${year}-${month}-${day}`;
    }

    search.addEventListener('input', function () {
        const month = currentDateObj.getMonth() + 1;
        const year = currentDateObj.getFullYear();
        const searchValue = search.value;
        loadAttendanceData(month, year, searchValue);
        $staffTable.bootstrapTable('refresh');
    });
});

/* ============================================================
       13
       . SAVE ATTENDANCE LOGIC
    ============================================================ */
$(document).on('click', '#saveAttendanceBtn', function () {
    const isBatchMode = $('#multipleStaff').val()
    const selectedDate = $('#selectedDate').val();
    const attendanceData = [];
    const $staffTable = $('.staff_table');
    const isHolidayChecked = $('#holiday').is(':checked');

    if (!selectedDate) {
        Swal.fire('Error', window.trans['please_select_a_valid_date_first'], 'error');
        return;
    }

    if (isHolidayChecked) {
        const tableData = $staffTable.bootstrapTable('getData');
        if (tableData.length === 0) {
            Swal.fire('Warning', window.trans['no_staff_records_found_to_mark_as_holiday'], 'warning');
            return;
        }

        tableData.forEach(row => {
            attendanceData.push({
                id: row.id ?? null,
                staff_id: row.staff_id,
                type: 3,
                reason: null,
                leave_id: null
            });
        });
    }

    // === 1️⃣ Single staff mode ===
    else if (isBatchMode == 0) {
        const staffName = $('#staffName').text();
        const date = $('#attendanceDate').text();
        const status = $('input[name="attendance_status"]:checked').val();
        const halfDayType = $('input[name="half_day_type"]:checked').val();
        const reason = $('#reason').val().trim();
        const isHolidayChecked = $('#holiday').is(':checked');

        const staffRow = $(`.mark-btn[data-name="${staffName}"][data-date="${date}"]`).closest('tr');
        const staffId = $('#staffIds').val(); // Assuming hidden input exists in table
        const attendanceId = $('#attendanceIds').val(); // Assuming hidden input exists in table

        if (!staffId) {
            Swal.fire('Error', window.trans['staff_id_not_found_for_this_record'], 'error');
            return;
        }

        // Determine numeric type
        let type = 0;
        switch (status) {
            case 'present': type = 1; break;
            case 'absent': type = 0; break;
            case 'half_day': type = halfDayType === 'first_half' ? 4 : 5; break;
            default: type = 1;
        }

        attendanceData.push({
            id: attendanceId,
            staff_id: staffId,
            type: type,
            reason: reason || null,
            leave_id: null
        });
    }

    // === 2️⃣ Batch mode ===
    else {
        const staffIds = $('#staffIds').val().split(',').map(id => id.trim()).filter(Boolean);
        const attendanceIds = $('#attendanceIds').val() ? $('#attendanceIds').val().split(',') : [];
        const status = $('input[name="attendance_status"]:checked').val();

        // 🔧 Define type before using it
        let type = 0;
        switch (status) {
            case 'present': type = 1; break;
            case 'absent': type = 0; break;
            default: type = 1;
        }

        staffIds.forEach((sid, index) => {
            attendanceData.push({
                id: attendanceIds[index] ?? null,
                staff_id: sid,
                type: type,
                reason: null,
                leave_id: null
            });
        });
    }


    if (attendanceData.length === 0) {
        Swal.fire('Warning', window.trans['no_staff_selected_to_save'], 'warning');
        return;
    }

    // === 3️⃣ Send AJAX request ===
    $.ajax({
        url: '/staff-attendance',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            date: selectedDate,
            holiday: $('#holiday').is(':checked') ? 3 : null,
            attendance_data: attendanceData,
            absent_notification: true
        },
        beforeSend: function () {
            $('#saveAttendanceBtn').prop('disabled', true).text('Saving...');
        },
        success: function (response) {
            $('#markAttendanceModal').modal('hide');
            if (response.error) {
                showErrorToast(response.message);
            } else {
                showSuccessToast(response.message || 'Attendance saved successfully.');
            }
            $('.staff_table input[type="checkbox"]').prop('checked', false);
            $('.staff_table input[type="checkbox"]').trigger('change');
            $('#multipleStaff').val(0);
            $('.staff_table').bootstrapTable('refresh');
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Failed to save attendance.'
            });
        },
        complete: function () {
            $('#saveAttendanceBtn').prop('disabled', false).text('Save Attendance');
        }
    });
});