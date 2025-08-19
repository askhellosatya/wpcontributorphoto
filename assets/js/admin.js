(function ($) {
  "use strict";

  $(function () {
    initCopyToClipboard();
    initCacheClear();
    initDismissibleNotice();
    initHelpModal();
    initRangeSliders();
    initColumnSelection();
    initStylingControls();
    initUserIdValidation();
    initSaveSettingsFeedback();
    initNoticeDismissal();

    setTimeout(initializeSelectedStates, 300);
  });

  // Debug toggle (false in production)
  var CPG_DEBUG = false;
  function cpgLog() {
    if (CPG_DEBUG && window.console) console.log.apply(console, arguments);
  }

  // Debounce helper for preview requests
  function cpgDebounce(fn, wait) {
    var t;
    return function () {
      var ctx = this,
        args = arguments;
      clearTimeout(t);
      t = setTimeout(function () {
        fn.apply(ctx, args);
      }, wait);
    };
  }
  var requestPreview = cpgDebounce(refreshGalleryPreview, 200);

  // ARIA live region for screen readers
  function ensureAriaLive() {
    var $live = $("#wpcpg-aria-live");
    if ($live.length === 0) {
      $live = $(
        '<div id="wpcpg-aria-live" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>'
      );
      $("body").append($live);
    }
    return $live;
  }

  // Copy to clipboard
  function initCopyToClipboard() {
    $(document).on("click", ".cpg-copy-btn", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var code =
        $btn.data("code") ||
        $btn
          .closest(".cpg-shortcode-block, .cpg-usage-shortcode")
          .find(".cpg-code, .cpg-shortcode-display")
          .text();
      if (!code) return;

      function onCopied() {
        var original = $btn.text();
        $btn.data("orig-text", original);
        $btn.text("Copied").prop("disabled", true).addClass("is-copied");

        var $live = ensureAriaLive();
        $live.text("Shortcode copied to clipboard");

        setTimeout(function () {
          $btn.text(original).prop("disabled", false).removeClass("is-copied");
          $live.text("");
        }, 1800);
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
          .writeText(code)
          .then(onCopied)
          .catch(function () {
            fallbackCopyText(code, onCopied);
          });
      } else {
        fallbackCopyText(code, onCopied);
      }
    });
  }

  function fallbackCopyText(text, onSuccess) {
    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val(text).select();
    try {
      document.execCommand("copy");
      if (typeof onSuccess === "function") onSuccess();
    } catch (e) {
      alert("Copy failed. Please copy manually.");
    } finally {
      $temp.remove();
    }
  }

  // Dismissible one-time notice
  function initDismissibleNotice() {
    $(document).on("click", ".wpcpg-notice-dismiss", function (e) {
      e.preventDefault();
      var $notice = $(this).closest("[data-notice]");
      $notice.slideUp(220, function () {
        $(this).remove();
      });

      $.post(wpcpgAdmin.ajaxurl, {
        action: "cpg_dismiss_new_shortcode_notice",
        nonce: wpcpgAdmin.nonce,
      });
    });

    $(document).on("click", ".wpcpg-notice-update-btn", function (e) {
      e.preventDefault();
      var code = $(this).attr("data-copy-shortcode") || "[cp_gallery]";
      var $btn = $(this);
      function afterCopy() {
        var orig = $btn.text();
        $btn.text("Copied");
        setTimeout(function () {
          $btn.text(orig);
        }, 1600);
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
          .writeText(code)
          .then(afterCopy)
          .catch(function () {
            fallbackCopyText(code, afterCopy);
          });
      } else {
        fallbackCopyText(code, afterCopy);
      }
    });
  }

  // Help modal
  function initHelpModal() {
    $(document).on(
      "click",
      "#user-id-help-btn, #user-id-guide-toggle",
      function (e) {
        e.preventDefault();
        showHelpModal(e.currentTarget);
      }
    );

    $(document).on("keydown", function (e) {
      if (e.key === "Escape") closeHelpModal();
    });

    $(document).on("click", ".wpcpg-modal-close", function () {
      closeHelpModal();
    });

    $(document).on("click", ".wpcpg-modal-overlay", function () {
      closeHelpModal();
    });
  }

  function showHelpModal(triggerElement) {
    var $existing = $("#wpcpg-help-modal");
    var $trigger = $(triggerElement || document.activeElement);

    if ($existing.length === 0) {
      var modalHtml =
        "" +
        '<div id="wpcpg-help-modal" class="wpcpg-modal" role="dialog" aria-modal="true" aria-labelledby="wpcpg-help-title" aria-describedby="wpcpg-help-desc" tabindex="-1">' +
        '  <div class="wpcpg-modal-overlay" tabindex="-1"></div>' +
        '  <div class="wpcpg-modal-panel wpcpg-modal-compact" role="document">' +
        '    <header class="wpcpg-modal-header compact">' +
        '      <h2 id="wpcpg-help-title">How to find your WordPress.org User ID</h2>' +
        '      <button class="wpcpg-modal-close" aria-label="Close dialog">&times;</button>' +
        "    </header>" +
        '    <div class="wpcpg-modal-body compact" id="wpcpg-help-desc">' +
        '      <div class="wpcpg-important-note compact"><strong>Important:</strong> Your WordPress.org User ID is a numeric ID — not your username.</div>' +
        '      <ol class="wpcpg-help-steps compact">' +
        '        <li><strong>Visit your author page:</strong><div class="wpcpg-code-inline">https://wordpress.org/photos/author/YOUR-USERNAME/</div></li>' +
        '        <li><strong>View page source:</strong><div class="wpcpg-verb">Right-click → "View page source" or press <kbd>Ctrl+U</kbd></div></li>' +
        '        <li><strong>Search for User ID:</strong><div class="wpcpg-verb">Press <kbd>Ctrl+F</kbd>, search for <code>wp-json/wp/v2/users/</code>. The number after that path is your ID.</div></li>' +
        "      </ol>" +
        '      <div class="wpcpg-example-box compact"><strong>Example</strong><div>Username: <span class="wpcpg-inline-code">hellosatya</span></div><div>User ID: <span class="wpcpg-inline-code">21053005</span></div></div>';
      $("body").append(modalHtml);
    }

    var $modal = $("#wpcpg-help-modal");
    $("body").addClass("wpcpg-modal-open");

    $modal.fadeIn(160, function () {
      var $firstFocusable = $modal
        .find(".wpcpg-modal-panel .wpcpg-modal-close")
        .first();
      if ($firstFocusable.length) {
        $firstFocusable.focus();
      } else {
        $modal.find(".wpcpg-modal-panel").attr("tabindex", "-1").focus();
      }
    });

    $modal.data("wpcpg-opener", $trigger);

    $modal
      .off("keydown.wpcpg-focustrap")
      .on("keydown.wpcpg-focustrap", function (e) {
        if (e.key !== "Tab") return;
        var $focusable = $modal
          .find(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          )
          .filter(":visible");
        if ($focusable.length === 0) return;
        var first = $focusable.first()[0];
        var last = $focusable.last();
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
    var $modal = $("#wpcpg-help-modal");
    if ($modal.length === 0) return;
    $modal.fadeOut(120, function () {
      $("body").removeClass("wpcpg-modal-open");
      var $opener = $modal.data("wpcpg-opener");
      if ($opener && $opener.length) {
        try {
          $opener.focus();
        } catch (e) {}
      }
    });
  }

  // Clear Cache
  function initCacheClear() {
    $(document).on("click", ".wpcpg-clear-cache", function (e) {
      e.preventDefault();
      var $btn = $(this);
      if ($btn.data("in-progress")) return;

      $btn.data("in-progress", true);
      var orig = $btn.text();
      $btn
        .text("Clearing…")
        .prop("disabled", true)
        .append(
          '<span class="spinner is-active" style="margin-left:8px"></span>'
        );

      $.post(
        wpcpgAdmin.ajaxurl,
        {
          action: "wpcpg_clear_cache",
          nonce: wpcpgAdmin.nonce,
        },
        function (resp) {
          if (resp && resp.success) {
            showAdminNotice(
              resp.data && resp.data.message
                ? resp.data.message
                : "Cache cleared",
              "success"
            );
          } else {
            showAdminNotice("Failed to clear cache", "error");
          }
        }
      )
        .fail(function () {
          showAdminNotice("Network error while clearing cache", "error");
        })
        .always(function () {
          $btn.data("in-progress", false).prop("disabled", false).text(orig);
          $btn.find(".spinner").remove();
        });
    });
  }

  function showAdminNotice(message, type) {
    type = type || "success";
    var $wrap = $("#wpcpg-admin-notices");
    if ($wrap.length === 0) {
      $wrap = $('<div id="wpcpg-admin-notices" />').insertBefore(
        ".wpcpg-cache-card"
      );
    }
    var $notice = $('<div class="notice inline wpcpg-notice" />')
      .addClass(type === "error" ? "notice-error" : "notice-success")
      .html("<strong>" + message + "</strong>");
    $wrap.prepend($notice.hide().slideDown(160));
    setTimeout(function () {
      $notice.slideUp(300, function () {
        $(this).remove();
      });
    }, 4000);
  }

  function initRangeSliders() {
    $(".cpg-range-slider").each(function () {
      var $slider = $(this);
      var $target = $("#per_page_display");
      var $hiddenInput = $("#default_per_page");

      // Update display on input
      $slider.on("input", function () {
        var value = $(this).val();
        $target.text(value);
        $hiddenInput.val(value);
        // Trigger preview update for photos per page changes
        if ($(this).attr("id") === "cpg_per_page") {
          requestPreview();
        }
      });

      // Set initial value
      $target.text($slider.val());
    });
  }

  // Columns
  function initColumnSelection() {
    $(".cpg-column-option").on("click", function () {
      var $this = $(this);
      var $radio = $this.find('input[type="radio"]');
      $(".cpg-column-option").removeClass("selected");
      $this.addClass("selected");
      $radio.prop("checked", true);
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });

    $(".cpg-column-radio").on("change", function () {
      var $option = $(this).closest(".cpg-column-option");
      $(".cpg-column-option").removeClass("selected");
      $option.addClass("selected");
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });
  }

  // Styling
  function initStylingControls() {
    $(".cpg-style-option").on("click", function () {
      var $this = $(this);
      var $radio = $this.find('input[type="radio"]');
      $(".cpg-style-option").removeClass("selected");
      $this.addClass("selected");
      $radio.prop("checked", true);
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });

    $('.cpg-style-option input[type="radio"]').on("change", function () {
      var $option = $(this).closest(".cpg-style-option");
      $(".cpg-style-option").removeClass("selected");
      $option.addClass("selected");
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });

    $(".cpg-shadow-option").on("click", function () {
      var $this = $(this);
      var $radio = $this.find('input[type="radio"]');
      $(".cpg-shadow-option").removeClass("selected");
      $this.addClass("selected");
      $radio.prop("checked", true);
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });

    $('.cpg-shadow-option input[type="radio"]').on("change", function () {
      var $option = $(this).closest(".cpg-shadow-option");
      $(".cpg-shadow-option").removeClass("selected");
      $option.addClass("selected");
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });

    $(
      '.cpg-color-picker, .cpg-style-selector select, .cpg-style-selector input[type="checkbox"]'
    ).on("change", function () {
      if ($("#default_user_id").val().trim() !== "") requestPreview();
    });
  }

  // User ID validation
  function initUserIdValidation() {
    var $userIdField = $("#default_user_id");
    var $statusDiv = $("#user-id-status");
    var $previewCard = $(".cpg-preview-card");
    if ($userIdField.length === 0) return;

    function validateUserId(userId) {
      var numericRegex = /^[1-9]\d{0,9}$/;
      return numericRegex.test(userId);
    }

    function updateValidationStatus(isValid, message, type) {
      $statusDiv.removeClass("valid invalid").addClass(type);
      $statusDiv.html(
        '<span class="dashicons dashicons-' +
          (isValid ? "yes" : "no") +
          '"></span> ' +
          message
      );

      if (isValid && $userIdField.val().trim() !== "") {
        $previewCard.show();
        requestPreview();
      } else {
        $previewCard.hide();
      }
    }

    $userIdField.on("input", function () {
      var userId = $(this).val().trim();
      if (userId === "") {
        $statusDiv.removeClass("valid invalid").html("");
        $previewCard.hide();
        return;
      }
      if (validateUserId(userId)) {
        updateValidationStatus(true, "Valid User ID format", "valid");
      } else {
        updateValidationStatus(
          false,
          "User ID must be numeric, greater than 0, and up to 10 digits",
          "invalid"
        );
      }
    });

    if ($userIdField.val().trim() !== "") $userIdField.trigger("input");
  }

  // Preview refresh
  function refreshGalleryPreview() {
    var $preview = $("#cpg-live-preview");
    if ($preview.length === 0) return;

    var $form = $(".cpg-form");
    var formData = $form.serialize();

    $preview.html(
      '<div class="cpg-loading"><span class="spinner is-active"></span> Updating preview...</div>'
    );

    $.post(
      wpcpgAdmin.ajaxurl,
      {
        action: "cpg_refresh_preview",
        settings: formData,
        nonce: wpcpgAdmin.nonce,
      },
      function (response) {
        if (response && response.success) {
          // Expect a string payload
          if (typeof response.data === "string") {
            $preview.html(response.data);
          } else if (response.data && response.data.html) {
            $preview.html(response.data.html);
          } else {
            $preview.html(
              '<div class="cpg-error">Invalid preview response</div>'
            );
          }
        } else {
          var msg =
            response && response.data
              ? response.data
              : "Failed to update preview";
          $preview.html('<div class="cpg-error">' + msg + "</div>");
        }
      }
    ).fail(function () {
      $preview.html(
        '<div class="cpg-error">Network error updating preview</div>'
      );
    });
  }

  // Notice dismissal
  function initNoticeDismissal() {
    $(document).on("click", ".cpg-shortcode-notice-dismiss", function (e) {
      e.preventDefault();
      var $notice = $(this).closest(".cpg-shortcode-notice");
      if ($notice.length) {
        $notice.fadeOut(200, function () {
          $(this).remove();
        });
        $.post(wpcpgAdmin.ajaxurl, {
          action: "cpg_dismiss_shortcode_notice",
          nonce: wpcpgAdmin.nonce,
        });
      }
    });
  }

  // Save feedback (optimistic visual)
  function initSaveSettingsFeedback() {
    $(".cpg-form").on("submit", function () {
      var $form = $(this);
      var $submitBtn = $form.find(".cpg-btn-save");
      var originalText = $submitBtn.text();

      // Ensure sliders sync hidden inputs before submission
      $(".cpg-range-slider").each(function () {
        var $slider = $(this);
        var $display = $slider.siblings(".cpg-range-value").find("span");
        var $hiddenInput = $slider.siblings('input[type="hidden"]');
        var currentValue = $slider.val();
        $hiddenInput.val(currentValue);
        $display.text(currentValue);
      });

      $submitBtn
        .prop("disabled", true)
        .html('<span class="spinner is-active"></span> Saving...')
        .addClass("saving");

      // Let Settings API redirect/reload handle the final state.
      setTimeout(function () {
        $submitBtn
          .prop("disabled", false)
          .html('<span class="dashicons dashicons-yes"></span> Settings Saved!')
          .removeClass("saving")
          .addClass("saved");

        setTimeout(function () {
          $submitBtn.text(originalText).removeClass("saved");
        }, 3000);
      }, 900);
    });
  }

  // Initialize selected states
  function initializeSelectedStates() {
    $(".cpg-column-option").each(function () {
      var $radio = $(this).find('input[type="radio"]');
      if ($radio.is(":checked")) $(this).addClass("selected");
    });
    $(".cpg-style-option").each(function () {
      var $radio = $(this).find('input[type="radio"]');
      if ($radio.is(":checked")) $(this).addClass("selected");
    });
    $(".cpg-shadow-option").each(function () {
      var $radio = $(this).find('input[type="radio"]');
      if ($radio.is(":checked")) $(this).addClass("selected");
    });
  }
})(jQuery);
