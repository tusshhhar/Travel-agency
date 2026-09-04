/**
 * Bishnoi Travels - Frontend Interactivity & Live Fare Engine
 */

document.addEventListener('DOMContentLoaded', () => {
  initThemeToggle();
  initMobileNav();
  initTripTabs();
  initDynamicFareCalculator();
  initWhatsAppSimulator();
});

/* Dark / Light Mode Toggle */
function initThemeToggle() {
  const toggleBtn = document.getElementById('theme_toggle_btn');
  if (!toggleBtn) return;

  toggleBtn.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('bt_theme', newTheme);
  });
}

/* Mobile Nav Toggle */
function initMobileNav() {
  const toggleBtn = document.querySelector('.mobile-nav-toggle');
  const navMenu = document.querySelector('.nav-menu');
  if (toggleBtn && navMenu) {
    toggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navMenu.classList.toggle('active');
      toggleBtn.innerHTML = navMenu.classList.contains('active') ? '&times;' : '&#9776;';
    });

    document.addEventListener('click', (e) => {
      if (navMenu.classList.contains('active') && !navMenu.contains(e.target) && e.target !== toggleBtn) {
        navMenu.classList.remove('active');
        toggleBtn.innerHTML = '&#9776;';
      }
    });

    navMenu.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        toggleBtn.innerHTML = '&#9776;';
      });
    });
  }
}

/* Trip Type Tabs Switching */
function initTripTabs() {
  const tabBtns = document.querySelectorAll('.trip-tab-btn');
  const tripTypeInput = document.getElementById('trip_type_input');
  const returnGroup = document.getElementById('return_date_group');
  const returnDateInput = document.getElementById('return_date');
  const pickupLabel = document.querySelector('label[for="pickup_location"]');
  const pickupInput = document.getElementById('pickup_location');
  const dropLabel = document.querySelector('label[for="drop_location"]');
  const dropInput = document.getElementById('drop_location');
  
  tabBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      
      const tripType = btn.getAttribute('data-trip');
      if (tripTypeInput) tripTypeInput.value = tripType;
      
      if (returnGroup) {
        if (tripType === 'Round Trip') {
          returnGroup.style.display = 'grid';
          if (returnDateInput) returnDateInput.required = true;
        } else {
          returnGroup.style.display = 'none';
          if (returnDateInput) {
            returnDateInput.required = false;
            returnDateInput.value = '';
          }
        }
      }

      // Dynamic adjustment of labels & placeholders based on trip type
      if (tripType === 'Local') {
        if (dropLabel) dropLabel.innerHTML = '📍 Local Tour / Package *';
        if (dropInput) dropInput.placeholder = 'e.g. Haridwar Local Sightseeing (80 KM)';
      } else if (tripType === 'Airport Transfer') {
        if (dropLabel) dropLabel.innerHTML = '📍 Airport / Drop Location *';
        if (dropInput) dropInput.placeholder = 'e.g. IGI Delhi Airport / Jolly Grant Dehradun';
      } else if (tripType === 'Round Trip') {
        if (dropLabel) dropLabel.innerHTML = '📍 Destination (Round Trip) *';
        if (dropInput) dropInput.placeholder = 'e.g. Delhi / Rishikesh / Mussoorie / Agra';
      } else { // One Way
        if (dropLabel) dropLabel.innerHTML = '📍 Drop Location *';
        if (dropInput) dropInput.placeholder = 'e.g. IGI Airport Delhi / Rishikesh / Dehradun';
      }
      
      triggerFareCalculation();
    });
  });
}

/* Dynamic Live Fare Calculator */
function initDynamicFareCalculator() {
  const pickupInput = document.getElementById('pickup_location');
  const dropInput = document.getElementById('drop_location');
  const vehicleSelect = document.getElementById('vehicle_id');
  const timeInput = document.getElementById('pickup_time');
  
  const triggers = [pickupInput, dropInput, vehicleSelect, timeInput];
  triggers.forEach(el => {
    if (el) {
      el.addEventListener('change', triggerFareCalculation);
      el.addEventListener('input', debounce(triggerFareCalculation, 400));
    }
  });

  // Initial check on page load: only calculate if both locations are already filled
  triggerFareCalculation();
}

function triggerFareCalculation() {
  const pickup = document.getElementById('pickup_location')?.value.trim() || '';
  const drop = document.getElementById('drop_location')?.value.trim() || '';
  const vehicleId = document.getElementById('vehicle_id')?.value || '';
  const tripType = document.getElementById('trip_type_input')?.value || 'One Way';
  const pickupTime = document.getElementById('pickup_time')?.value || '08:00 AM';
  const fareBox = document.getElementById('live_fare_display');

  if (!fareBox) return;

  // When location fields are empty, hide the pricing breakdown completely!
  if (!pickup || !drop || !vehicleId) {
    fareBox.style.display = 'none';
    return;
  }

  // Display the fare box once both pickup and drop are entered
  fareBox.style.display = 'block';
  fareBox.style.opacity = '0.5';

  fetch(`api/calculate_fare.php?pickup=${encodeURIComponent(pickup)}&drop=${encodeURIComponent(drop)}&vehicle_id=${encodeURIComponent(vehicleId)}&trip_type=${encodeURIComponent(tripType)}&pickup_time=${encodeURIComponent(pickupTime)}`)
    .then(res => res.json())
    .then(data => {
      fareBox.style.opacity = '1';
      if (data.success) {
        if (document.getElementById('est_distance_txt')) {
          document.getElementById('est_distance_txt').textContent = `${data.estimated_distance} KM`;
        }
        if (document.getElementById('est_rate_txt')) {
          document.getElementById('est_rate_txt').textContent = `₹${data.per_km_rate}/KM`;
        }
        if (document.getElementById('base_fare_txt')) {
          document.getElementById('base_fare_txt').textContent = `₹${data.base_fare}`;
        }
        if (document.getElementById('distance_charge_txt')) {
          document.getElementById('distance_charge_txt').textContent = `₹${data.distance_charge}`;
        }
        
        const daRow = document.getElementById('driver_allowance_row');
        if (daRow) {
          daRow.style.display = data.driver_allowance > 0 ? 'flex' : 'none';
          document.getElementById('driver_allowance_txt').textContent = `₹${data.driver_allowance}`;
        }
        
        const ncRow = document.getElementById('night_charge_row');
        if (ncRow) {
          ncRow.style.display = data.night_charge > 0 ? 'flex' : 'none';
          document.getElementById('night_charge_txt').textContent = `₹${data.night_charge}`;
        }

        const tollRow = document.getElementById('toll_tax_row');
        if (tollRow) {
          tollRow.style.display = data.toll_tax_charge > 0 ? 'flex' : 'none';
          document.getElementById('toll_tax_txt').textContent = `₹${data.toll_tax_charge}`;
        }

        document.getElementById('total_fare_txt').textContent = `₹${data.total_amount.toLocaleString()}`;
        const advTxt = document.getElementById('advance_pay_txt');
        if (advTxt) advTxt.textContent = `₹${data.advance_amount.toLocaleString()}`;
      }
    })
    .catch(err => {
      fareBox.style.opacity = '1';
      console.warn("Fare calc offline fallback:", err);
    });
}

/* Debounce Helper */
function debounce(func, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

/* WhatsApp Live Interactive Chat Simulator (FR-015 to FR-020) */
function initWhatsAppSimulator() {
  const triggerBtn = document.getElementById('whatsapp_widget_btn');
  const chatModal = document.getElementById('whatsapp_chat_modal');
  const closeBtn = document.getElementById('chat_close_btn');
  const chatForm = document.getElementById('chat_input_form');
  const chatInput = document.getElementById('chat_text_input');
  const chatBody = document.getElementById('chat_body');

  if (!triggerBtn || !chatModal) return;

  triggerBtn.addEventListener('click', () => {
    chatModal.classList.toggle('open');
    if (chatModal.classList.contains('open')) {
      if (chatBody && chatBody.children.length === 0) {
        // First greeting
        appendBotMessage("🚕 *Welcome to Jambho Haridwar Travels!*\nAll Over India 24 Hours Cab Services.\n\nHow can we help you today?\n1️⃣ Book a Cab\n2️⃣ Check Fare\n3️⃣ Track Booking\n4️⃣ Call Support");
      }
      chatInput?.focus();
    }
  });

  closeBtn?.addEventListener('click', () => {
    chatModal.classList.remove('open');
  });

  // Quick reply button handlers
  document.querySelectorAll('.quick-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const msg = btn.getAttribute('data-reply');
      sendUserMessage(msg);
    });
  });

  chatForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const msg = chatInput.value.trim();
    if (!msg) return;
    sendUserMessage(msg);
    chatInput.value = '';
  });

  function sendUserMessage(msg) {
    appendUserMessage(msg);

    // Show typing indicator
    const typingElem = document.createElement('div');
    typingElem.className = 'chat-msg msg-bot';
    typingElem.id = 'chat_typing';
    typingElem.textContent = 'Typing... 🚕';
    chatBody.appendChild(typingElem);
    chatBody.scrollTop = chatBody.scrollHeight;

    fetch('api/whatsapp_simulate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: msg, phone: '919536200261' })
    })
    .then(res => res.json())
    .then(data => {
      const typ = document.getElementById('chat_typing');
      if (typ) typ.remove();
      if (data.reply) {
        appendBotMessage(data.reply);
      }
    })
    .catch(err => {
      const typ = document.getElementById('chat_typing');
      if (typ) typ.remove();
      appendBotMessage("Thank you for contacting Jambho Haridwar Travels! For urgent cab booking call 9536200261.");
    });
  }

  function appendUserMessage(text) {
    const el = document.createElement('div');
    el.className = 'chat-msg msg-user';
    el.textContent = text;
    chatBody.appendChild(el);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function appendBotMessage(text) {
    const el = document.createElement('div');
    el.className = 'chat-msg msg-bot';
    // Format bold markdown
    let formatted = text.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
    formatted = formatted.replace(/\n/g, '<br>');
    el.innerHTML = formatted;
    chatBody.appendChild(el);
    chatBody.scrollTop = chatBody.scrollHeight;
  }
}
