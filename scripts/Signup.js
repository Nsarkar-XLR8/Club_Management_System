document.getElementById("signupForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  const name = document.getElementById("signupUsername").value;
  const email = document.getElementById("signupEmail").value;
  const password = document.getElementById("signupPassword").value;
  const confirm = document.getElementById("signupConfirmPassword").value;

  if (password !== confirm) {
    document.getElementById("signupError").textContent = "Passwords do not match";
    return;
  }

  const res = await fetch("signup.php", {
    method: "POST",
    body: new URLSearchParams({ name, email, password })
  });
  const data = await res.json();
  if (data.success) {
    window.location.href = "Login.html";
  } else {
    document.getElementById("signupError").textContent = data.error;
  }
});
