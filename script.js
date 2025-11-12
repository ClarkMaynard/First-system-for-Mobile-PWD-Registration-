// script.js
// Basic client-side required-field check. This prevents accidental submits.
// Keep server-side validation as well (never trust client-side only).

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById("pwdForm");
  if (!form) return;

  form.addEventListener("submit", function(event) {
    const fields = ["FullName", "DateOfBirth", "Gender", "DisabilityType", "Username", "Password"];
    let valid = true;

    fields.forEach(field => {
      const input = document.querySelector(`[name="${field}"]`);
      if (!input || !input.value.trim()) {
        if (input) input.style.border = "2px solid red";
        valid = false;
      } else {
        input.style.border = "1px solid #333";
      }
    });

    if (!valid) {
      event.preventDefault();
      alert("Please fill in all required fields before submission.");
      return false;
    }

    // Optionally, perform basic password-length check
    const pw = document.querySelector('[name="Password"]');
    if (pw && pw.value.length < 6) {
      event.preventDefault();
      alert("Password must be at least 6 characters.");
      pw.style.border = "2px solid red";
      return false;
    }
  });
});
