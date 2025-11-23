document.getElementById("loginForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;

  const res = await fetch("login.php", {
    method: "POST",
    body: new URLSearchParams({ email, password })
  });
  const data = await res.json();
  if (data.success) {
    localStorage.setItem("user", JSON.stringify(data.user));
    window.location.href = "home.html";
  } else {
    document.getElementById("loginError").textContent = data.error;
  }
});
