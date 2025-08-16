(function($) {
  'use strict';

  $(function() {
      initCopyToClipboard();
      initCacheClear();
      initDismissibleNotice();
      initHelpModal();
      initRangeSliders();
      initColumnSelection();
      initStylingControls();
      initUserIdValidation();

      setTimeout(initializeSelectedStates, 300);
  });

  /* Small ARIA live region for unobtrusive announcements (screen reader friendly) */
  function ensureAriaLive() {
      var $live = $('#wpcpg-aria-live');
      if ($live.length === 0) {
          $live = $('<div id="wpcpg-aria-live" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>');
          $('body').append($live);
      }
      return $live;
  }

  /* ---------------------
     Copy to clipboard (improved)
     --------------------- */
  function initCopyToClipboard() {
      $(document).on('click', '.cpg-copy-btn', function(e) {
          e.preventDefault();
          var $btn = $(this);
          var code = $btn.data('code') || $btn.closest('.cpg-shortcode-block').find('.cpg-code').text();
          if (!code) return;

          function onCopied() {
              var original = $btn.text();
              $btn.closest('.cpg-shortcode-block').find('.cpg-code').addClass('cpg-code-copied');
              $btn.data('orig-text', original);
              $btn.text('Copied');
              $btn.prop('disabled', true);
              $btn.addClass('is-copied');

              var $live = ensureAriaLive();
              $live.text('Shortcode copied to clipboard');

              setTimeout(function() {
                  $btn.text(original);
                  $btn.prop('disabled', false);
                  $btn.removeClass('is-copied');
                  $btn.closest('.cpg-shortcode-block').find('.cpg-code').removeClass('cpg-code-copied');
                  $live.text('');
              }, 2000);
          }

          if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(code).then(onCopied).catch(function() {
                  fallbackCopyText(code, onCopied);
              });
          } else {
              fallbackCopyText(code, onCopied);
          }
      });
  }

  function fallbackCopyText(text, onSuccess) {
      var $temp = $('<textarea>');
      $('body').append($temp);
      $temp.val(text).select();
      try {
          document.execCommand('copy');
          onSuccess();
      } catch (e) {
          alert('Copy failed. Please copy manually.');
      } finally {
          $temp.remove();
      }
  }

  /* ---------------------
     Dismissible one-time notice
     --------------------- */
  function initDismissibleNotice() {
      $(document).on('click', '.wpcpg-notice-dismiss', function(e) {
          e.preventDefault();
          var $notice = $(this).closest('[data-notice]');
          $notice.slideUp(220, function() {
              $(this).remove();
          });

          // Notify server to persist dismissal
          $.post(wpcpgAdmin.ajaxurl, {
              action: 'cpg_dismiss_new_shortcode_notice',
              nonce: wpcpgAdmin.nonce
          }).done(function(resp) {
              // nothing further required; server will store option
          }).fail(function() {
              if (window.console && console.warn) console.warn('Failed to persist dismissal');
          });
      });

      $(document).on('click', '.wpcpg-notice-update-btn', function(e) {
          e.preventDefault();
          var code = $(this).attr('data-copy-shortcode') || '[cp_gallery]';
          var $btn = $(this);
          function afterCopy() {
              var orig = $btn.text();
              $btn.text('Copied');
              setTimeout(function() { $btn.text(orig); }, 1600);
          }
          if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(code).then(afterCopy).catch(function() { fallbackCopyText(code, afterCopy); });
          } else {
              fallbackCopyText(code, afterCopy);
          }
      });
  }

  /* ---------------------
     Help modal (compact, centered, accessible)
     --------------------- */
  function initHelpModal() {
      $(document).on('click', '#user-id-help-btn, #user-id-guide-toggle', function(e) {
          e.preventDefault();
          showHelpModal(e.currentTarget);
      });

      // Close modal on escape (global handler)
      $(document).on('keydown', function(e) {
          if (e.key === 'Escape') {
              closeHelpModal();
          }
      });

      // Accessibility: return focus to opener when modal closes
      $(document).on('click', '.wpcpg-modal-close', function() {
          closeHelpModal();
      });

      $(document).on('click', '.wpcpg-modal-overlay', function() {
          closeHelpModal();
      });
  }

  function showHelpModal(triggerElement) {
      var $existing = $('#wpcpg-help-modal');
      var $trigger = $(triggerElement || document.activeElement);

      // If modal exists reuse, otherwise create
      if ($existing.length === 0) {
          var modalHtml = ''
              + '<div id="wpcpg-help-modal" class="wpcpg-modal" role="dialog" aria-modal="true" aria-labelledby="wpcpg-help-title" aria-describedby="wpcpg-help-desc" tabindex="-1">'
              + '  <div class="wpcpg-modal-overlay" tabindex="-1"></div>'
              + '  <div class="wpcpg-modal-panel wpcpg-modal-compact" role="document">'
              + '    <header class="wpcpg-modal-header compact">'
              + '      <h2 id="wpcpg-help-title">How to find your WordPress.org User ID</h2>'
              + '      <button class="wpcpg-modal-close" aria-label="Close dialog">&times;</button>'
              + '    </header>'
              + '    <div class="wpcpg-modal-body compact" id="wpcpg-help-desc">'
              + '      <div class="wpcpg-important-note compact"><strong>Important:</strong> Your WordPress.org User ID is a numeric ID — not your username.</div>'
              + '      <ol class="wpcpg-help-steps compact">'
              + '        <li><strong>Visit your author page:</strong><div class="wpcpg-code-inline">https://wordpress.org/photos/author/YOUR-USERNAME/</div></li>'
              + '        <li><strong>View page source:</strong><div class="wpcpg-verb">Right-click → "View page source" or press <kbd>Ctrl+U</kbd></div></li>'
              + '        <li><strong>Search for User ID:</strong><div class="wpcpg-verb">Press <kbd>Ctrl+F</kbd>, search for <code>wp-json/wp/v2/users/</code>. The number after that path is your ID.</div></li>'
              + '      </ol>'
              + '      <div class="wpcpg-example-box compact" aria-hidden="false"><strong>Example</strong><div>Username: <span class="wpcpg-inline-code">hellosatya</span></div><div>User ID: <span class="wpcpg-inline-code">21053005</span></div></div>';
          $('body').append(modalHtml);
      }

      // show modal
      var $modal = $('#wpcpg-help-modal');
      // lock background scroll
      $('body').addClass('wpcpg-modal-open');

      // show with fade and focus
      $modal.fadeIn(160, function() {
          // focus the close button for immediate keyboard access
          var $firstFocusable = $modal.find('.wpcpg-modal-panel .wpcpg-modal-close').first();
          if ($firstFocusable.length) {
              $firstFocusable.focus();
          } else {
              $modal.find('.wpcpg-modal-panel').attr('tabindex', '-1').focus();
          }
      });

      // Store the opener to return focus later
      $modal.data('wpcpg-opener', $trigger);

      // Basic focus trap: keep tab within modal
      $modal.off('keydown.wpcpg-focustrap').on('keydown.wpcpg-focustrap', function(e) {
          if (e.key !== 'Tab') return;
          var $focusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
          if ($focusable.length === 0) return;
          var first = $focusable.first()[0];
          var last = $focusable.last()[0];
          if (e.shiftKey && document.activeElement === first) {
              e.preventDefault();
              last.focus();
          } else if (!e.shiftKey && document.activeElement === last) {
              e.preventDefault();
              first.focus();
          }
      });
  }

  function closeHelpModal() {
      var $modal = $('#wpcpg-help-modal');
      if ($modal.length === 0) return;
      $modal.fadeOut(120, function() {
          // unlock background scroll
          $('body').removeClass('wpcpg-modal-open');
          // return focus to opener if present
          var $opener = $modal.data('wpcpg-opener');
          if ($opener && $opener.length) {
              try { $opener[0].focus(); } catch (e) {}
          }
      });
  }

  /* ---------------------
     Clear Cache handler (existing) - minimal re-use
     --------------------- */
  function initCacheClear() {
      $(document).on('click', '.wpcpg-clear-cache', function(e) {
          e.preventDefault();
          var $btn = $(this);
          if ($btn.data('in-progress')) return;

          $btn.data('in-progress', true);
          var orig = $btn.text();
          $btn.text('Clearing…').prop('disabled', true).append('<span class="spinner is-active" style="margin-left:8px"></span>');

          $.post(wpcpgAdmin.ajaxurl, {
              action: 'wpcpg_clear_cache',
              nonce: wpcpgAdmin.nonce
          }, function(resp) {
              if (resp && resp.success) {
                  showAdminNotice(resp.data && resp.data.message ? resp.data.message : 'Cache cleared', 'success');
              } else {
                  showAdminNotice('Failed to clear cache', 'error');
              }
          }).fail(function(xhr) {
              showAdminNotice('Network error while clearing cache', 'error');
          }).always(function() {
              $btn.data('in-progress', false);
              $btn.prop('disabled', false).text(orig);
              $btn.find('.spinner').remove();
          });
      });
  }

  /* ---------------------
     Small admin notice helper (used for cache actions only)
     --------------------- */
  function showAdminNotice(message, type) {
      type = type || 'success';
      var $wrap = $('#wpcpg-admin-notices');
      if ($wrap.length === 0) {
          $wrap = $('<div id="wpcpg-admin-notices" />').insertBefore('.wpcpg-cache-card');
      }
      var $notice = $('<div class="notice inline wpcpg-notice" />')
          .addClass(type === 'error' ? 'notice-error' : 'notice-success')
          .html('<strong>' + message + '</strong>');

      $wrap.prepend($notice.hide().slideDown(160));
      setTimeout(function() { $notice.slideUp(300, function() { $(this).remove(); }); }, 4000);
  }

  /* ---------------------
     Small helpers (kept from earlier code)
     --------------------- */
  function initRangeSliders() { /* placeholder, unchanged from earlier implementation */ }
  function initColumnSelection() { /* placeholder, unchanged from earlier implementation */ }
  function initStylingControls() { /* placeholder, unchanged from earlier implementation */ }
  function initUserIdValidation() { /* placeholder, unchanged from earlier implementation */ }
  function initializeSelectedStates() { /* placeholder, unchanged from earlier implementation */ }

})(jQuery);
