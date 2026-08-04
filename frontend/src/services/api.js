// API Service Layer for UIU CMS
const API_BASE = '/api';

const getHeaders = () => {
  const token = localStorage.getItem('uiu_cms_token');
  return {
    'Content-Type': 'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {})
  };
};

export const api = {
  // Auth API
  login: async (email, password) => {
    const res = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ email, password })
    });
    return res.json();
  },

  register: async (student_id, name, email, password, department) => {
    const res = await fetch(`${API_BASE}/auth/register`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ student_id, name, email, password, department })
    });
    return res.json();
  },

  getMe: async () => {
    const res = await fetch(`${API_BASE}/auth/me`, {
      headers: getHeaders()
    });
    return res.json();
  },

  // Clubs API
  getClubs: async () => {
    const res = await fetch(`${API_BASE}/clubs`);
    return res.json();
  },

  getClubDetails: async (id) => {
    const res = await fetch(`${API_BASE}/clubs/${id}`);
    return res.json();
  },

  joinClub: async (clubId, position = 'General Member') => {
    const res = await fetch(`${API_BASE}/clubs/${clubId}/members`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ position })
    });
    return res.json();
  },

  // Elections API (Restricted to verified members of specific club)
  getElections: async () => {
    const res = await fetch(`${API_BASE}/elections`);
    return res.json();
  },

  castVote: async (election_id, candidate_id) => {
    const res = await fetch(`${API_BASE}/elections/vote`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ election_id, candidate_id })
    });
    return res.json();
  },

  // Facilities API
  getFacilities: async () => {
    const res = await fetch(`${API_BASE}/facilities`);
    return res.json();
  },

  bookFacility: async (bookingData) => {
    const res = await fetch(`${API_BASE}/facilities/book`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(bookingData)
    });
    return res.json();
  },

  approveFacility: async (booking_id, status) => {
    const res = await fetch(`${API_BASE}/facilities/approve`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify({ booking_id, status })
    });
    return res.json();
  },

  // Announcements API
  getAnnouncements: async () => {
    const res = await fetch(`${API_BASE}/announcements`);
    return res.json();
  },

  createAnnouncement: async (noticeData) => {
    const res = await fetch(`${API_BASE}/announcements/create`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(noticeData)
    });
    return res.json();
  },

  // Alumni API
  getAlumni: async () => {
    const res = await fetch(`${API_BASE}/alumni`);
    return res.json();
  },

  requestMentorship: async (alumni_id, topic, message) => {
    const res = await fetch(`${API_BASE}/alumni/mentorship-request`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ alumni_id, topic, message })
    });
    return res.json();
  },

  // Events API
  getEvents: async () => {
    const res = await fetch(`${API_BASE}/events`);
    return res.json();
  },

  createEvent: async (eventData) => {
    const res = await fetch(`${API_BASE}/events`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(eventData)
    });
    return res.json();
  },

  registerEvent: async (eventId) => {
    const res = await fetch(`${API_BASE}/events/${eventId}/register`, {
      method: 'POST',
      headers: getHeaders()
    });
    return res.json();
  },

  checkinQR: async (qrToken) => {
    const res = await fetch(`${API_BASE}/events/checkin`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ qr_code_token: qrToken })
    });
    return res.json();
  },

  // Forum API
  getForumTopics: async () => {
    const res = await fetch(`${API_BASE}/forum/topics`);
    return res.json();
  },

  createForumTopic: async (topicData) => {
    const res = await fetch(`${API_BASE}/forum/topics`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(topicData)
    });
    return res.json();
  },

  // Donors API
  searchDonors: async (bloodGroup = '') => {
    const res = await fetch(`${API_BASE}/donors/search?blood_group=${encodeURIComponent(bloodGroup)}`);
    return res.json();
  },

  registerDonor: async (donorData) => {
    const res = await fetch(`${API_BASE}/donors/register`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(donorData)
    });
    return res.json();
  },

  // Budgets API
  getBudgets: async () => {
    const res = await fetch(`${API_BASE}/budgets`, {
      headers: getHeaders()
    });
    return res.json();
  },

  requestBudget: async (budgetData) => {
    const res = await fetch(`${API_BASE}/budgets/request`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(budgetData)
    });
    return res.json();
  },

  reviewBudget: async (budget_id, status, approved_amount, remarks) => {
    const res = await fetch(`${API_BASE}/budgets/review`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify({ budget_id, status, approved_amount, review_remarks: remarks })
    });
    return res.json();
  }
};
