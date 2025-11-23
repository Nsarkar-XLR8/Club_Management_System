// Home.js
document.addEventListener("DOMContentLoaded", () => {
  loadRecentEvents();
  loadUpcomingEvents();
});

async function loadRecentEvents() {
  const container = document.getElementById("recentEvents");
  if (!container) return;

  try {
    const res = await fetch("getEvents.php?type=recent");
    const events = await res.json();

    container.innerHTML = "<h2>Recent Events</h2>";
    events.forEach(ev => {
      container.innerHTML += `
        <div class="event-card">
          <img src="${ev.banner_url || './images/random2.jpg'}" alt="${ev.title}">
          <p><strong>${ev.title}</strong> — ${ev.event_date}<br>${ev.description}</p>
        </div>`;
    });
  } catch (err) {
    console.error("Error loading recent events", err);
  }
}

async function loadUpcomingEvents() {
  const list = document.getElementById("upcomingEventsList");
  if (!list) return;

  try {
    const res = await fetch("getEvents.php?type=upcoming");
    const events = await res.json();

    list.innerHTML = "";
    events.forEach(ev => {
      list.innerHTML += `<li>🎉 ${ev.title} — ${ev.event_date}, ${ev.location}</li>`;
    });
  } catch (err) {
    console.error("Error loading upcoming events", err);
  }
}
