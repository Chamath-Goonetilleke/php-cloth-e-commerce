// Admin Panel JavaScript

$(document).ready(function () {
  // Toggle sidebar
  $("#toggle-sidebar").on("click", function () {
    $("body").toggleClass("sidebar-collapsed");

    // Save state in local storage
    if ($("body").hasClass("sidebar-collapsed")) {
      localStorage.setItem("sidebar-collapsed", "true");
    } else {
      localStorage.setItem("sidebar-collapsed", "false");
    }
  });

  // Check sidebar state on page load
  if (localStorage.getItem("sidebar-collapsed") === "true") {
    $("body").addClass("sidebar-collapsed");
  }

  // Mobile sidebar toggle
  $(".toggle-btn").on("click", function (e) {
    if (window.innerWidth < 992) {
      e.stopPropagation();
      $("body").toggleClass("sidebar-open");
    }
  });

  // Close sidebar when clicking outside on mobile
  $(document).on("click", function (e) {
    if (
      window.innerWidth < 992 &&
      $("body").hasClass("sidebar-open") &&
      !$(e.target).closest(".sidebar").length
    ) {
      $("body").removeClass("sidebar-open");
    }
  });

  // Initialize dropdown menus
  $(".profile-dropdown").on("click", function (e) {
    e.stopPropagation();
    $(this).find(".dropdown-menu").toggleClass("active");
  });

  // Close dropdown when clicking outside
  $(document).on("click", function () {
    $(".dropdown-menu").removeClass("active");
  });

  // File input custom styling
  $(".custom-file-input").on("change", function () {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
  });

  // Initialize tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Auto-hide alerts after 5 seconds
  setTimeout(function () {
    $(".alert").fadeOut("slow");
  }, 5000);

  // Confirmation for delete actions
  $(".confirm-delete").on("click", function (e) {
    if (
      !confirm(
        "Are you sure you want to delete this item? This action cannot be undone."
      )
    ) {
      e.preventDefault();
    }
  });

  // Form validation
  $(".needs-validation").each(function () {
    $(this).on("submit", function (event) {
      if (this.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }
      $(this).addClass("was-validated");
    });
  });

  // Image preview before upload
  $(".image-upload").on("change", function () {
    var input = this;
    var preview = $(this).data("preview");

    if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
        $("#" + preview)
          .attr("src", e.target.result)
          .show();
      };

      reader.readAsDataURL(input.files[0]);
    }
  });

  // Toggle password visibility
  $(".toggle-password").on("click", function () {
    var input = $($(this).data("toggle"));
    if (input.attr("type") === "password") {
      input.attr("type", "text");
      $(this).find("i").removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
      input.attr("type", "password");
      $(this).find("i").removeClass("fa-eye-slash").addClass("fa-eye");
    }
  });

  // Initialize select2 if available
  if ($.fn.select2) {
    $(".select2").select2({
      theme: "bootstrap4",
      width: "100%",
    });
  }

  // Initialize datepicker if available
  if ($.fn.datepicker) {
    $(".datepicker").datepicker({
      format: "yyyy-mm-dd",
      autoclose: true,
      todayHighlight: true,
    });
  }

  // Initialize CKEditor if available
  if (typeof CKEDITOR !== "undefined") {
    $(".ckeditor").each(function () {
      CKEDITOR.replace($(this).attr("id"));
    });
  }
});
