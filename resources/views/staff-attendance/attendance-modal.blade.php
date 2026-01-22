<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-labelledby="markAttendanceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="markAttendanceLabel">Mark Attendance</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="multiple_staff" id="multipleStaff">
                <input type="hidden" name="staff_id" id="staffIds">
                <input type="hidden" name="attendance_ids" id="attendanceIds">
                <!-- Staff info -->
                <p class="text-muted mb-4">
                    <strong id="staffName"></strong> •
                    <span id="attendanceDate"></span>
                </p>

                <!-- Attendance Status -->
                <div class="attendance-status-group mb-4">
                    <label class="attendance-option">
                        <input type="radio" name="attendance_status" value="present" checked>
                        <div class="option-body">
                            <h6 class="fw-semibold mb-0">Present</h6>
                            <small class="text-muted">Full working day</small>
                        </div>
                    </label>

                    <label class="attendance-option">
                        <input type="radio" name="attendance_status" value="absent">
                        <div class="option-body">
                            <h6 class="fw-semibold mb-0">Absent</h6>
                            <small class="text-muted">Will automatically create a leave entry</small>
                        </div>
                    </label>

                    <label class="attendance-option" id="halfDayOption">
                        <input type="radio" name="attendance_status" value="half_day">
                        <div class="option-body">
                            <h6 class="fw-semibold mb-0">Half Day</h6>
                            <small class="text-muted">0.5 day present</small>
                        </div>
                    </label>
                </div>

                <!-- Half-Day Selection -->
                <div id="halfDaySelection" class="ms-4 mb-4 d-none">
                    <p class="fw-semibold mb-2">Which half was present?</p>
                    <label class="attendance-option sub-option">
                        <input type="radio" name="half_day_type" value="first_half">
                        <div class="option-body">
                            <h6 class="fw-semibold mb-0">First Half</h6>
                            <small class="text-muted">Morning session present</small>
                        </div>
                    </label>

                    <label class="attendance-option sub-option">
                        <input type="radio" name="half_day_type" value="second_half">
                        <div class="option-body">
                            <h6 class="fw-semibold mb-0">Second Half</h6>
                            <small class="text-muted">Afternoon session present</small>
                        </div>
                    </label>
                </div>

                <!-- Reason -->
                <div class="mb-4 reasonSection">
                    <label class="form-label fw-semibold">Reason <span class="text-muted">(Optional)</span></label>
                    <textarea class="form-control" id="reason" rows="2"
                        placeholder="E.g., Came late, Left early, Personal work, Medical emergency..."></textarea>
                    <small class="text-muted d-block mt-2">
                        This helps in maintaining clarity for audits and reviews
                    </small>
                </div>

                <!-- Footer buttons -->
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-dark mr-2" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-successed" id="saveAttendanceBtn">Save Attendance</button>
                </div>
            </div>
        </div>
    </div>
</div>