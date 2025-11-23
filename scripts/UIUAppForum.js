// UIUAppForum.js
document.addEventListener("DOMContentLoaded", () => {
  loadClubMembers("uiu-app-forum");
  loadClubEvents("uiu-app-forum");
});

async function loadClubMembers(clubSlug) {
  const container = document.getElementById("clubMembers");
  if (!container) return;

  const res = await fetch(`getMembers.php?club=${clubSlug}`);
  const members = await res.json();

  container.innerHTML = "<h2>Members Details</h2>";
  members.forEach(m => {
    container.innerHTML += `
      <div class="member-card">
        <img src="./images/PresidentProfile1.png" alt="${m.name}">
        <div><h3>${m.name}</h3><p>${m.role}</p></div>
      </div>`;
  });
}

async function loadClubEvents(clubSlug) {
  const container = document.getElementById("clubEvents");
  if (!container) return;

  const res = await fetch(`getEvents.php?club=${clubSlug}`);
  const events = await res.json();

  container.innerHTML = "<h2>Recent Events</h2>";
  events.forEach(ev => {
    container.innerHTML += `
      <div class="event-card">
        <img src="${ev.banner_url || './images/uiucsefest.jpg'}" alt="${ev.title}">
        <p><strong>${ev.title}</strong> — ${ev.event_date}<br>${ev.description}</p>
      </div>`;
  });
}
