(function () {
  var residentMain = document.querySelector('.resident-layout');
  if (residentMain && !document.getElementById('residentConnectionNotice')) {
    var connectionNotice = document.createElement('p');
    connectionNotice.id = 'residentConnectionNotice';
    connectionNotice.className = 'resident-connection-notice';
    connectionNotice.setAttribute('role', 'status');
    connectionNotice.setAttribute('aria-live', 'polite');
    connectionNotice.hidden = navigator.onLine;
    connectionNotice.textContent = 'You are offline. Live updates and new submissions may be unavailable; use the saved emergency guide if needed.';
    residentMain.insertBefore(connectionNotice, residentMain.firstChild);
    window.addEventListener('offline', function () {
      connectionNotice.hidden = false;
    });
    window.addEventListener('online', function () {
      connectionNotice.hidden = true;
    });
  }

  var reportForm = document.getElementById('communityReportForm');
  var photoInput = document.getElementById('reportPhoto');
  var photoStatus = document.getElementById('reportPhotoStatus');
  var submitStatus = document.getElementById('reportSubmitStatus');
  var submitButton = document.getElementById('reportSubmit');

  if (photoInput && photoStatus) {
    photoInput.addEventListener('change', function () {
      var file = photoInput.files && photoInput.files[0];
      photoInput.setCustomValidity('');
      if (!file) {
        photoStatus.textContent = 'No photo selected.';
        return;
      }
      if (file.size > 3 * 1024 * 1024) {
        photoInput.setCustomValidity('Choose an image no larger than 3 MB.');
        photoStatus.textContent = 'This image is larger than 3 MB. Choose a smaller photo.';
        photoInput.reportValidity();
        return;
      }
      var typeAllowed = ['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) !== -1;
      var extensionAllowed = /\.(jpe?g|png|webp)$/i.test(file.name);
      if (!typeAllowed && !(!file.type && extensionAllowed)) {
        photoInput.setCustomValidity('Choose a JPEG, PNG, or WebP image.');
        photoStatus.textContent = 'Unsupported file type. Choose a JPEG, PNG, or WebP image.';
        photoInput.reportValidity();
        return;
      }
      photoStatus.textContent = file.name + ' · ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB';
    });
  }

  if (reportForm && submitButton && submitStatus) {
    reportForm.addEventListener('submit', function (event) {
      if (photoInput && !photoInput.reportValidity()) {
        event.preventDefault();
        return;
      }
      submitButton.disabled = true;
      submitButton.textContent = 'Submitting report…';
      submitStatus.hidden = false;
      submitStatus.textContent = 'Submitting your report. Please keep this page open until it finishes.';
    });
  }

  var centerSearch = document.getElementById('centerSearch');
  var searchStatus = document.getElementById('centerSearchStatus');
  var centerCards = Array.prototype.slice.call(document.querySelectorAll('.evacuation-card[data-center-search]'));
  var emptySearchState = document.getElementById('evacuationSearchEmpty');

  if (centerSearch && searchStatus) {
    centerSearch.addEventListener('input', function () {
      var query = centerSearch.value.trim().toLocaleLowerCase();
      var visible = 0;
      centerCards.forEach(function (card) {
        var matches = card.getAttribute('data-center-search').toLocaleLowerCase().indexOf(query) !== -1;
        card.hidden = !matches;
        if (matches) visible += 1;
      });
      searchStatus.textContent = query
        ? visible + (visible === 1 ? ' location found' : ' locations found')
        : visible + (visible === 1 ? ' location listed' : ' locations listed');
      if (emptySearchState) emptySearchState.hidden = visible !== 0;
    });
  }

  var announcementQuickLinks = Array.prototype.slice.call(
    document.querySelectorAll('.resident-portal.announce-page .bottom-nav a[href^="#"]')
  );
  if (announcementQuickLinks.length) {
    var updateAnnouncementQuickNavigation = function () {
      var activeHash = window.location.hash || '#notices';
      announcementQuickLinks.forEach(function (link) {
        var active = link.getAttribute('href') === activeHash;
        link.classList.toggle('is-active', active);
        if (active) {
          link.setAttribute('aria-current', 'page');
        } else {
          link.removeAttribute('aria-current');
        }
      });
    };
    announcementQuickLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        window.setTimeout(updateAnnouncementQuickNavigation, 0);
      });
    });
    window.addEventListener('hashchange', updateAnnouncementQuickNavigation);
    updateAnnouncementQuickNavigation();
  }
})();
