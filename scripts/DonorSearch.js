document.getElementById("searchBtn").addEventListener("click", async function(e) {
  e.preventDefault();
  const bloodGroup = document.getElementById("bloodGroup").value;
  const city = document.getElementById("city").value;
  const area = document.getElementById("area").value;

  const res = await fetch("searchDonors.php?" + new URLSearchParams({ bloodGroup, city, area }));
  const donors = await res.json();

  const tbody = document.getElementById("donorResults");
  tbody.innerHTML = "";
  if (!donors.length) {
    document.getElementById("noResults").style.display = "block";
    return;
  }
  document.getElementById("noResults").style.display = "none";
  donors.forEach(d => {
    tbody.innerHTML += `<tr>
      <td>${d.name}</td>
      <td>${d.city}, ${d.area}</td>
      <td>${d.email}</td>
      <td>${d.phone}</td>
    </tr>`;
  });
});
