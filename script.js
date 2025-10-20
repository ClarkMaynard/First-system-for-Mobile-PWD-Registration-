document.getElementById("pwdForm").addEventListener("submit", function(event) {
  const fields = ["FullName", "DateOfBirth", "Gender", "DisabilityType", "Username", "Password"];
  let valid = true;

  fields.forEach(field => {
    const input = document.querySelector(`[name="${field}"]`);
    if (!input.value.trim()) {
      input.style.border = "2px solid red";
      valid = false;
    } else {
      input.style.border = "1px solid #333";
    }
  });

  if (!valid) {
    event.preventDefault();
    alert("Please fill in all required fields before submission.");
  }
});
