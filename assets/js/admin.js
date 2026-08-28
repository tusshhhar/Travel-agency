/**
 * Bishnoi Travels - Admin Panel Scripts
 */

function openAssignModal(bookingId, currentDriverId) {
  const modal = document.getElementById('assignDriverModal');
  const inputBookingId = document.getElementById('modal_booking_id');
  const selectDriver = document.getElementById('modal_driver_id');
  
  if (modal && inputBookingId) {
    inputBookingId.value = bookingId;
    if (selectDriver && currentDriverId) {
      selectDriver.value = currentDriverId;
    }
    modal.classList.add('open');
  }
}

function openStatusModal(bookingId, currentStatus) {
  const modal = document.getElementById('updateStatusModal');
  const inputBookingId = document.getElementById('status_modal_booking_id');
  const selectStatus = document.getElementById('status_modal_status');
  
  if (modal && inputBookingId) {
    inputBookingId.value = bookingId;
    if (selectStatus) {
      selectStatus.value = currentStatus;
    }
    modal.classList.add('open');
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('open');
  }
}

// Close on backdrop click
window.addEventListener('click', (e) => {
  if (e.target.classList.contains('admin-modal-backdrop')) {
    e.target.classList.remove('open');
  }
});
