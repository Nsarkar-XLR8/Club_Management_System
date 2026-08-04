import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  LayoutDashboard, 
  Users, 
  Calendar, 
  DollarSign, 
  QrCode, 
  Plus, 
  CheckCircle2, 
  XCircle, 
  Clock, 
  ShieldCheck, 
  TrendingUp,
  FileText,
  Camera,
  AlertCircle
} from 'lucide-react';

export default function DashboardPage() {
  const { user } = useAuth();
  const [activeSubTab, setActiveSubTab] = useState('overview');
  const containerRef = useRef(null);
  
  usePageReveal(containerRef, [activeSubTab]); // GSAP animations applied to container, re-runs on tab change
  
  // Data states
  const [budgets, setBudgets] = useState([]);
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);

  // QR Scanner simulation
  const [qrToken, setQrToken] = useState('');
  const [scanResult, setScanResult] = useState(null);
  const [scanning, setScanning] = useState(false);

  // New Event Form
  const [eventTitle, setEventTitle] = useState('');
  const [eventDesc, setEventDesc] = useState('');
  const [eventDate, setEventDate] = useState('');
  const [eventLocation, setEventLocation] = useState('UIU Multipurpose Hall');
  const [eventMsg, setEventMsg] = useState('');

  // Budget Request Form
  const [reqTitle, setReqTitle] = useState('');
  const [reqDesc, setReqDesc] = useState('');
  const [reqAmount, setReqAmount] = useState('');
  const [reqMsg, setReqMsg] = useState('');

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = () => {
    setLoading(true);
    Promise.all([api.getBudgets(), api.getEvents()])
      .then(([bData, eData]) => {
        if (bData.status === 'success') setBudgets(bData.budgets);
        if (eData.status === 'success') setEvents(eData.events);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const handleScanQR = async (e) => {
    e.preventDefault();
    setScanning(true);
    setScanResult(null);

    const res = await api.checkinQR(qrToken);
    setScanResult(res);
    setScanning(false);
  };

  const handleCreateEvent = async (e) => {
    e.preventDefault();
    const res = await api.createEvent({
      club_id: 1,
      title: eventTitle,
      description: eventDesc,
      event_date: eventDate,
      location: eventLocation
    });
    if (res.status === 'success') {
      setEventMsg('Event created successfully!');
      setEventTitle('');
      setEventDesc('');
      fetchDashboardData();
    }
  };

  const handleRequestBudget = async (e) => {
    e.preventDefault();
    const res = await api.requestBudget({
      club_id: 1,
      title: reqTitle,
      description: reqDesc,
      requested_amount: parseFloat(reqAmount)
    });
    if (res.status === 'success') {
      setReqMsg('Budget request submitted to Faculty Advisor!');
      setReqTitle('');
      setReqDesc('');
      setReqAmount('');
      fetchDashboardData();
    }
  };

  const handleReviewBudget = async (budgetId, status, amount) => {
    const res = await api.reviewBudget(budgetId, status, amount, 'Reviewed by Faculty Advisor');
    if (res.status === 'success') {
      fetchDashboardData();
    }
  };

  return (
    <div ref={containerRef} className="space-y-8 pb-16">
      
      {/* Header */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
          <div className="gsap-stagger inline-flex items-center gap-2 text-xs font-semibold text-[#F26522] mb-1">
            <ShieldCheck className="w-4 h-4" /> Role-Based Control Center ({user?.role?.toUpperCase()})
          </div>
          <h1 className="text-2xl sm:text-4xl font-extrabold text-slate-900 font-serif">Executive Dashboard</h1>
          <p className="gsap-stagger text-xs text-slate-500 mt-2">Manage event entries, financial budget approvals, and campus executive workflows.</p>
        </div>

        <div className="gsap-stagger flex items-center gap-2">
          <span className="px-3 py-1.5 rounded-lg bg-orange-50 text-[#F26522] border border-orange-200 text-xs font-bold shadow-sm">
            {user?.name} ({user?.role})
          </span>
        </div>
      </div>

      {/* Sub Tabs */}
      <div className="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
        {[
          { id: 'overview', label: 'Overview & Budgets', icon: LayoutDashboard },
          { id: 'scanner', label: 'Gate QR Scanner', icon: QrCode },
          { id: 'add_event', label: 'Create Event', icon: Plus },
          { id: 'req_budget', label: 'Request Budget', icon: DollarSign },
        ].map((tab) => {
          const Icon = tab.icon;
          const isActive = activeSubTab === tab.id;
          return (
            <button
              key={tab.id}
              onClick={() => setActiveSubTab(tab.id)}
              className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 shadow-sm ${
                isActive
                  ? 'bg-gradient-to-r from-[#F26522] to-[#D95316] text-white'
                  : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 hover:bg-slate-50'
              }`}
            >
              <Icon className="w-4 h-4" />
              {tab.label}
            </button>
          );
        })}
      </div>

      {/* 1. OVERVIEW & BUDGET APPROVALS */}
      {activeSubTab === 'overview' && (
        <div className="space-y-8">
          
          {/* Summary Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div className="gsap-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
              <div className="text-xs text-slate-500 font-semibold mb-1 uppercase tracking-wider">Total Active Events</div>
              <div className="text-3xl font-extrabold text-slate-900">{events.length}</div>
            </div>

            <div className="gsap-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
              <div className="text-xs text-slate-500 font-semibold mb-1 uppercase tracking-wider">Total Budget Requests</div>
              <div className="text-3xl font-extrabold text-[#003366]">{budgets.length}</div>
            </div>

            <div className="gsap-card p-6 rounded-2xl bg-white border border-emerald-200 shadow-sm hover:shadow-md transition-shadow bg-gradient-to-br from-white to-emerald-50/30">
              <div className="text-xs text-emerald-700 font-semibold mb-1 uppercase tracking-wider">Approved Funding</div>
              <div className="text-3xl font-extrabold text-emerald-600">
                ৳{budgets.reduce((acc, b) => acc + parseFloat(b.approved_amount || 0), 0).toLocaleString()}
              </div>
            </div>
          </div>

          {/* Budget Requests Table */}
          <div className="gsap-stagger p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <h3 className="text-lg font-bold text-slate-900 flex items-center justify-between font-serif">
              <span>Financial Budget Requests & Governance</span>
              <DollarSign className="w-5 h-5 text-emerald-600" />
            </h3>

            <div className="overflow-x-auto rounded-xl border border-slate-200">
              <table className="w-full text-left text-xs text-slate-700">
                <thead className="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200">
                  <tr>
                    <th className="p-3">Title</th>
                    <th className="p-3">Club</th>
                    <th className="p-3">Requested</th>
                    <th className="p-3">Approved</th>
                    <th className="p-3">Status</th>
                    <th className="p-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {budgets.map((b) => (
                    <tr key={b.id} className="hover:bg-slate-50 transition-colors bg-white">
                      <td className="p-3 font-bold text-slate-900">{b.title}</td>
                      <td className="p-3">{b.club_name}</td>
                      <td className="p-3 text-[#F26522] font-bold">৳{parseFloat(b.requested_amount).toLocaleString()}</td>
                      <td className="p-3 text-emerald-600 font-bold">৳{parseFloat(b.approved_amount).toLocaleString()}</td>
                      <td className="p-3">
                        <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase shadow-sm ${
                          b.status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                          b.status === 'rejected' ? 'bg-red-50 text-red-700 border border-red-200' :
                          'bg-amber-50 text-amber-700 border border-amber-200'
                        }`}>
                          {b.status}
                        </span>
                      </td>
                      <td className="p-3 text-right space-x-2">
                        {b.status === 'pending' && inRoles(user, [1, 2]) && (
                          <>
                            <button
                              onClick={() => handleReviewBudget(b.id, 'approved', b.requested_amount)}
                              className="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] shadow-sm transition-colors"
                            >
                              Approve
                            </button>
                            <button
                              onClick={() => handleReviewBudget(b.id, 'rejected', 0)}
                              className="px-2.5 py-1 rounded bg-red-600 hover:bg-red-700 text-white font-bold text-[10px] shadow-sm transition-colors"
                            >
                              Reject
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))}
                  {budgets.length === 0 && (
                    <tr>
                      <td colSpan="6" className="p-6 text-center text-slate-500 font-medium bg-white">No budget requests found.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>

        </div>
      )}

      {/* 2. GATE QR SCANNER */}
      {activeSubTab === 'scanner' && (
        <div className="gsap-stagger max-w-xl mx-auto p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-lg space-y-6 text-center">
          <div className="w-16 h-16 rounded-2xl bg-orange-50 border border-orange-200 text-[#F26522] flex items-center justify-center mx-auto shadow-sm">
            <Camera className="w-8 h-8" />
          </div>

          <div>
            <h3 className="text-xl font-bold text-slate-900 font-serif">Live Gate Entry Scanner</h3>
            <p className="text-xs text-slate-500 mt-1">Scan student event ticket token or enter manually to verify QR pass.</p>
          </div>

          <form onSubmit={handleScanQR} className="space-y-4">
            <input
              type="text"
              required
              placeholder="Paste QR Ticket Token (e.g. UIU-EVT-1-USR-5-...)"
              value={qrToken}
              onChange={(e) => setQrToken(e.target.value)}
              className="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono text-slate-900 text-center focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
            />

            <button
              type="submit"
              disabled={scanning}
              className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-xs text-white uppercase tracking-wider flex items-center justify-center gap-2"
            >
              {scanning ? 'Verifying...' : 'Verify Ticket & Check-in'}
            </button>
          </form>

          {scanResult && (
            <div className={`p-4 rounded-xl text-left border shadow-sm ${
              scanResult.status === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'
            }`}>
              <div className="font-bold text-sm flex items-center gap-2">
                {scanResult.status === 'success' ? <CheckCircle2 className="w-5 h-5 text-emerald-600" /> : <XCircle className="w-5 h-5 text-red-600" />}
                {scanResult.message}
              </div>
              {scanResult.attendee && (
                <div className="text-xs mt-3 space-y-2 bg-white/50 p-3 rounded-lg">
                  <div><strong className="text-slate-700">Student:</strong> {scanResult.attendee}</div>
                  <div><strong className="text-slate-700">Event:</strong> {scanResult.event}</div>
                </div>
              )}
            </div>
          )}
        </div>
      )}

      {/* 3. CREATE EVENT */}
      {activeSubTab === 'add_event' && (
        <div className="gsap-stagger max-w-xl mx-auto p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-lg space-y-6">
          <h3 className="text-xl font-bold text-slate-900 font-serif border-b border-slate-100 pb-4">Create New Campus Event</h3>

          {eventMsg && (
            <div className="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold shadow-sm">
              {eventMsg}
            </div>
          )}

          <form onSubmit={handleCreateEvent} className="space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Event Title</label>
              <input
                type="text"
                required
                placeholder="e.g. Competitive Programming Workshop"
                value={eventTitle}
                onChange={(e) => setEventTitle(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Event Date</label>
              <input
                type="date"
                required
                value={eventDate}
                onChange={(e) => setEventDate(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Location</label>
              <input
                type="text"
                value={eventLocation}
                onChange={(e) => setEventLocation(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Description</label>
              <textarea
                rows="3"
                value={eventDesc}
                onChange={(e) => setEventDesc(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              ></textarea>
            </div>

            <button
              type="submit"
              className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-xs text-white uppercase tracking-wider mt-4"
            >
              Publish Event
            </button>
          </form>
        </div>
      )}

      {/* 4. REQUEST BUDGET */}
      {activeSubTab === 'req_budget' && (
        <div className="gsap-stagger max-w-xl mx-auto p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-lg space-y-6">
          <h3 className="text-xl font-bold text-slate-900 font-serif border-b border-slate-100 pb-4">Submit Financial Budget Request</h3>

          {reqMsg && (
            <div className="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold shadow-sm">
              {reqMsg}
            </div>
          )}

          <form onSubmit={handleRequestBudget} className="space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Budget Request Title</label>
              <input
                type="text"
                required
                placeholder="e.g. Hackathon Catering & Prize Pool"
                value={reqTitle}
                onChange={(e) => setReqTitle(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Requested Amount (BDT ৳)</label>
              <input
                type="number"
                required
                placeholder="50000"
                value={reqAmount}
                onChange={(e) => setReqAmount(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Description / Breakdown</label>
              <textarea
                rows="3"
                value={reqDesc}
                onChange={(e) => setReqDesc(e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              ></textarea>
            </div>

            <button
              type="submit"
              className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-xs text-white uppercase tracking-wider mt-4"
            >
              Submit Request to Faculty
            </button>
          </form>
        </div>
      )}

    </div>
  );
}

function inRoles(user, roleIds) {
  return user && roleIds.includes(parseInt(user.role_id));
}
