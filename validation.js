$(document).ready(function () {
  const form = $("#userForm");
  const name = $("#name");
  const email = $("#email");
  const image = $("#image");

  const nameError = $("#nameError");
  const emailError = $("#emailError");
  const imageError = $("#imageError");

  const addMobileBtn = $("#addMobileBtn");
  const mobileContainer = $("#mobileNumbersContainer");

  // Name validation
  name.on("input", function () {
    const value = $(this).val().trim();
    if (value.length < 2) {
      nameError.text("Name must be at least 2 characters.");
    } else if (value.length > 20) {
      nameError.text("Name must be less than 20 characters.");
    } else {
      nameError.text("");
    }
  });

  // Email validation
  email.on("blur", function () {
    const value = $(this).val().trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(value)) {
      emailError.text("Invalid email format.");
      return;
    }

    // AJAX call to check if email exists
    $.ajax({
      url: "checkEmail.php",
      method: "POST",
      data: { email: value },
      success: function (response) {
        if (response.trim() === "exists") {
          emailError.text("Email already exists.");
        } else {
          emailError.text(""); // Clear error if available
        }
      },
      error: function () {
        emailError.text("Error checking email.");
      }
    });
  });

  // Image validation
  image.on("change", function () {
    const file = this.files[0];
    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/jpg"];
    if (file) {
      if (!allowedTypes.includes(file.type)) {
        imageError.text("Only JPG, JPEG, PNG, and GIF are allowed.");
      } else {
        imageError.text("");
      }
    } else {
      imageError.text("Please select an image.");
    }
  });

  // Validate mobile number
  function validateMobileInput($input) {
    const value = $input.val().trim();
    const pattern = /^[0-9]{10,15}$/;
    const $error = $input.siblings(".mobile-error");

    if (!value) {
      $error.text("Mobile number is required.");
      return false;
    } else if (!pattern.test(value)) {
      $error.text("Enter 10–15 digit number.");
      return false;
    } else {
      $error.text("");
      return true;
    }
  }

  // Attach validation to existing inputs
  $("input[name='mobile_numbers[]']").each(function () {
    $(this).on("input", function () {
      validateMobileInput($(this));
    });
  });

  // Add new mobile field
  addMobileBtn.on("click", function () {
    const newField = $(`
      <div class="mobile-field">
        <input type="text" name="mobile_numbers[]" placeholder="Enter mobile number" />
        <span class="mobile-error" style="color:red;"></span>
        <button type="button" class="removeMobileBtn">Remove</button>
      </div>
    `);

    mobileContainer.append(newField);

    const $input = newField.find("input");
    $input.on("input", function () {
      validateMobileInput($(this));
    });

    newField.find(".removeMobileBtn").on("click", function () {
      newField.remove();
    });
  });

  // Final form submit validation
  form.on("submit", function (e) {
    let allValid = true;

    $("input[name='mobile_numbers[]']").each(function () {
      if (!validateMobileInput($(this))) {
        allValid = false;
      }
    });

    if (
      nameError.text() ||
      emailError.text() ||
      imageError.text() ||
      !allValid ||
      name.val().trim() === "" ||
      email.val().trim() === "" ||
      image[0].files.length === 0
    ) {
      e.preventDefault();
      alert("Please fix all errors before submitting.");
    }
  });
});
