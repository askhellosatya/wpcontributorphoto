(function() {
    'use strict';
  
    var lastTrigger = null;
  
    document.addEventListener('DOMContentLoaded', function() {
      initPhotoGallery();
      initLazyLoading();
      initLightbox();
    });
  
    function initPhotoGallery() {
      var galleries = document.querySelectorAll('.cpg-gallery-grid');
      galleries.forEach(function(gallery) {
        addImageLoadingStates(gallery);
        handleResponsiveColumns(gallery);
        enhanceAccessibility(gallery);
      });
    }
  
    function addImageLoadingStates(gallery) {
      var images = gallery.querySelectorAll('img');
      images.forEach(function(img) {
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.3s ease';
  
        if (img.complete && img.naturalWidth !== 0) {
          img.style.opacity = '1';
        } else {
          img.addEventListener('load', function() {
            this.style.opacity = '1';
          });
          img.addEventListener('error', function() {
            this.style.opacity = '0.5';
            this.setAttribute('alt', 'Failed to load image');
          });
        }
      });
    }
  
    function handleResponsiveColumns(gallery) {
      function adjustColumns() {
        var width = window.innerWidth;
        var match = gallery.className.match(/columns-(\d+)/);
        var currentColumns = parseInt(match ? match[1] : 3, 10);
  
        if (width < 480) {
          gallery.style.gridTemplateColumns = 'repeat(1, 1fr)';
        } else if (width < 768) {
          gallery.style.gridTemplateColumns = 'repeat(' + Math.min(2, currentColumns) + ', 1fr)';
        } else if (width < 1024) {
          gallery.style.gridTemplateColumns = 'repeat(' + Math.min(3, currentColumns) + ', 1fr)';
        } else {
          gallery.style.gridTemplateColumns = 'repeat(' + currentColumns + ', 1fr)';
        }
      }
      adjustColumns();
      window.addEventListener('resize', throttle(adjustColumns, 250));
    }
  
    function enhanceAccessibility(gallery) {
      gallery.setAttribute('role', 'grid');
      gallery.setAttribute('aria-label', 'WordPress.org photo contributions gallery');
  
      var items = gallery.querySelectorAll('.cpg-photo-card');
      items.forEach(function(item, index) {
        item.setAttribute('role', 'gridcell');
        item.setAttribute('aria-setsize', items.length);
        item.setAttribute('aria-posinset', index + 1);
  
        var link = item.querySelector('a');
        if (link) link.setAttribute('aria-describedby', 'cpg-sr-desc');
      });
  
      if (!document.getElementById('cpg-sr-desc')) {
        var srDesc = document.createElement('div');
        srDesc.id = 'cpg-sr-desc';
        srDesc.className = 'screen-reader-text';
        srDesc.textContent = 'Link opens WordPress.org photo page in new tab';
        document.body.appendChild(srDesc);
      }
    }
  
    function initLazyLoading() {
      if ('IntersectionObserver' in window) {
        var imageObserver = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              var img = entry.target;
              if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
              }
            }
          });
        });
  
        document.querySelectorAll('.cpg-gallery-grid img[data-src]').forEach(function(img) {
          imageObserver.observe(img);
        });
      }
    }
  
    // Lightbox UX: normal click opens, Ctrl/Cmd/middle-click respects browser behavior
    function initLightbox() {
      var links = document.querySelectorAll('.cpg-gallery-grid .cpg-photo-card a');
      links.forEach(function(link) {
        link.addEventListener('click', function(e) {
          if (e.ctrlKey || e.metaKey || e.button === 1) return; // let browser handle new tab
          var img = this.querySelector('img');
          if (img) {
            e.preventDefault();
            showImageModal(img.src, img.alt);
          }
        });
      });
    }
  
    function showImageModal(src, alt) {
      var modal = document.getElementById('cpg-lightbox');
      if (!modal) modal = createLightboxModal();
  
      var img = modal.querySelector('.cpg-lightbox-img');
      var caption = modal.querySelector('.cpg-lightbox-caption');
  
      img.src = src;
      img.alt = alt || '';
      caption.textContent = alt || '';
  
      modal.style.display = 'flex';
      document.body.classList.add('cpg-modal-open');
      lastTrigger = document.activeElement || null;
      modal.focus();
    }
  
    function createLightboxModal() {
      var modal = document.createElement('div');
      modal.id = 'cpg-lightbox';
      modal.className = 'cpg-lightbox';
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('tabindex', '-1');
  
      modal.innerHTML = ''
        + '<div class="cpg-lightbox-overlay"></div>'
        + '<div class="cpg-lightbox-content">'
        + '<button type="button" class="cpg-lightbox-close" aria-label="Close lightbox">&times;</button>'
        + '<img class="cpg-lightbox-img" src="" alt="">'
        + '<div class="cpg-lightbox-caption"></div>'
        + '</div>';
  
      document.body.appendChild(modal);
  
      modal.querySelector('.cpg-lightbox-close').addEventListener('click', closeLightbox);
      modal.querySelector('.cpg-lightbox-overlay').addEventListener('click', closeLightbox);
  
      modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
      });
  
      return modal;
    }
  
    function closeLightbox() {
      var modal = document.getElementById('cpg-lightbox');
      if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('cpg-modal-open');
        if (lastTrigger && typeof lastTrigger.focus === 'function') {
          try { lastTrigger.focus(); } catch(e){}
        }
      }
    }
  
    function throttle(func, limit) {
      var inThrottle;
      return function() {
        var args = arguments;
        var context = this;
        if (!inThrottle) {
          func.apply(context, args);
          inThrottle = true;
          setTimeout(function() {
            inThrottle = false;
          }, limit);
        }
      };
    }
  })();  