document.getElementById("inquiryForm").addEventListener("submit", function (e) {
  e.preventDefault();

  // Clear error alert
  const errorAlert = document.getElementById("errorAlert");
  errorAlert.classList.add("d-none");
  errorAlert.innerHTML = "";

  // Read values
  const name = document.querySelector("input[name='name']").value.trim();
  const mobile = document
    .querySelector("input[name='contact']")
    .value.trim()
    .replace(/\D/g, "");
  const address = document.querySelector("input[name='address']").value.trim();
  const minPrice = document
    .querySelector("input[name='min_price']")
    .value.trim();

  // REQUIRED VALIDATION
  let errors = [];

  if (!name) errors.push("Please enter your Name.");
  if (!mobile) errors.push("Please enter Contact Number.");
  if (mobile.length < 10) errors.push("Contact Number must be 10 digits.");
  if (!address) errors.push("Please enter Address.");
  if (!minPrice) errors.push("Please enter Minimum Price.");

  if (errors.length > 0) {
    errorAlert.innerHTML = errors.join("<br>");
    errorAlert.classList.remove("d-none");
    window.scrollTo(0, 0);
    return;
  }

  // --- build formData ---
  const formData = {
    name: name,
    contact: mobile,
    email:
      document.querySelector("input[name='email']").value.trim() ||
      "noemail@greenwich.com",
    occupation: document.querySelector("input[name='occupation']").value.trim(),
    company: document.querySelector("input[name='company']").value.trim(),
    address: address,
    sizes: [],
    min_price: minPrice,
    max_price: document.querySelector("input[name='max_price']").value.trim(),
    source_list: Array.from(
      document.querySelectorAll(".option-card input:checked")
    )
      .map((cb) => cb.parentElement.innerText.trim())
      .join(", "),
    remarks: document.querySelector("textarea").value.trim(),
  };

  if (document.getElementById("size1").checked)
    formData.sizes.push("4 BHK");
  if (document.getElementById("size2").checked)
    formData.sizes.push("3 BHK + HT");

  // Submit to CRM
  fetch("crm.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(formData),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        new bootstrap.Modal(document.getElementById("thankYouModal")).show();
        document.getElementById("inquiryForm").reset();
      } else {
        alert("CRM Error: " + data.error);
      }
    })
    .catch((err) => console.error("Request Error:", err));
});
