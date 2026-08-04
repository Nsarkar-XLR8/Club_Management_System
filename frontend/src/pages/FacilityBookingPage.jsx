import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  Building2, 
  Calendar, 
  Clock, 
  MapPin, 
  CheckCircle2, 
  AlertTriangle, 
  Plus, 
  ShieldCheck,
  Zap,
  Check,
  X
} from 'lucide-react';

export default function FacilityBookingPage({ onOpenAuth }) {
  const { user } = useAuth();
  const [facilities, setFacilities] = useState([]);
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [msg, setMsg] = useState({ type: '', text: '' });
  const containerRef = useRef(null);

  usePageReveal(containerRef, [bookings, facilities]);

  // Booking Form
  const [facilityId, setFacilityId] = useState('');
  const [clubId, setClubId] = useState('');
  const [clubs, setClubs] = useState([]);
  const [bookingDate, setBookingDate] = useState('');
  const [startTime, setStartTime] = useState('09:00');
  const [endTime, setEndTime] = useState('12:00');
  const [purpose, setPurpose] = useState('');

  useEffect(() => {
    fetchFacilities();
    fetchClubs();
  }, []);

  const fetchFacilities = () => {
    setLoading(true);
    api.getFacilities()
      .then(data => {
        if (data.status === 'success') {
          setFacilities(data.facilities || []);
          setBookings(data.bookings || []);
          if (data.facilities && data.facilities.length > 0) {
            setFacilityId(data.facilities[0].id.toString());
          }
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const fetchClubs = () => {
    api.getClubs().then(data => {
      if (data.status === 'success') {
        setClubs(data.clubs || []);
        if (data.clubs && data.clubs.length > 0) {
          setClubId(data.clubs[0].id.toString());
        }
      }
    }).catch(() => {});
  };

  const handleBookFacility = async (e) => {
    e.preventDefault();
    if (!user) {
      onOpenAuth();
      return;
    }
    setMsg({ type: '', text: '' });
    const res = await api.bookFacility({
      facility_id: parseInt(facilityId),
      club_id: parseInt(clubId),
      booking_date: bookingDate,
      start_time: startTime,
      end_time: endTime,
      purpose
    });
    if (res.status === 'success') {
      setMsg({ type: 'success', text: res.message });
      setPurpose('');
      fetchFacilities();
    } else {
      setMsg({ type: 'error', text: res.message });
    }
  };

  const handleApprove = async (id, status) => {
    const res = await api.approveFacility(id, status);
    if (res.status === 'success') {
      fetchFacilities();
    }
  };

  return (
    <div ref={containerRef} className="space-y-10 pb-16">
      
      {/* Title */}
      <div className="text-center max-w-3xl mx-auto space-y-3">
        <div className="gsap-stagger inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-xs font-semibold text-[#F26522] border border-[#F26522]/20 shadow-sm">
          <Building2 className="w-4 h-4" /> DSA Venue & Equipment Scheduler
        </div>
        <h1 className="text-3xl sm:text-5xl font-extrabold text-slate-900 font-serif">
          Campus <span className="text-[#F26522]">Facility Booking</span>
        </h1>
        <p className="gsap-stagger text-sm text-slate-600">
          Reserve UIU Auditorium, Multipurpose Hall, Computer Labs, or Sound Equipment with automatic overlap conflict prevention.
        </p>
      </div>

      {msg.text && (
        <div className={`gsap-stagger max-w-xl mx-auto p-4 rounded-xl text-xs font-bold text-center flex items-center justify-center gap-2 shadow-sm ${
          msg.type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700'
        }`}>
          {msg.type === 'success' ? <CheckCircle2 className="w-5 h-5" /> : <AlertTriangle className="w-5 h-5" />}
          {msg.text}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {/* Booking Form */}
        <div className="gsap-stagger p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-lg space-y-6 h-fit">
          <h3 className="text-xl font-bold text-slate-900 flex items-center gap-2 font-serif border-b border-slate-100 pb-4">
            <Plus className="w-5 h-5 text-[#F26522]" /> Reserve Venue or Equipment
          </h3>

          <form onSubmit={handleBookFacility} className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Select Facility / Gear</label>
                <select
                  value={facilityId}
                  onChange={(e) => setFacilityId(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                >
                  {facilities.map(f => (
                    <option key={f.id} value={f.id}>
                      {f.name} ({f.type.toUpperCase()} • Cap: {f.capacity})
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Booking for Club</label>
                <select
                  value={clubId}
                  onChange={(e) => setClubId(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                >
                  {clubs.map(c => (
                    <option key={c.id} value={c.id}>
                      {c.name} ({c.code})
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Booking Date</label>
              <input
                type="date"
                required
                value={bookingDate}
                onChange={(e) => setBookingDate(e.target.value)}
                className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Start Time</label>
                <input
                  type="time"
                  required
                  value={startTime}
                  onChange={(e) => setStartTime(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">End Time</label>
                <input
                  type="time"
                  required
                  value={endTime}
                  onChange={(e) => setEndTime(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Event Purpose & Details</label>
              <textarea
                rows="3"
                required
                placeholder="e.g. UIU Inter-University Hackathon Opening Ceremony"
                value={purpose}
                onChange={(e) => setPurpose(e.target.value)}
                className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
              ></textarea>
            </div>

            <button
              type="submit"
              className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-xs text-white uppercase tracking-wider mt-4 shadow-md"
            >
              Submit Reservation Request
            </button>
          </form>
        </div>

        {/* Existing Reservations Feed */}
        <div className="lg:col-span-2 space-y-4">
          <h3 className="gsap-stagger text-xl font-bold text-slate-900 mb-4 flex items-center justify-between font-serif">
            <span>Live Campus Reservations Schedule</span>
            <span className="text-xs text-emerald-700 font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-200">Conflict-Free Engine Active</span>
          </h3>

          <div className="space-y-4">
            {bookings.length > 0 ? (
              bookings.map((b) => (
                <div key={b.id} className="gsap-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow space-y-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="text-base font-bold text-slate-900 font-serif">{b.facility_name}</span>
                      <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase shadow-sm ${
                        b.status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                        b.status === 'rejected' ? 'bg-red-50 text-red-700 border border-red-200' :
                        'bg-amber-50 text-amber-700 border border-amber-200'
                      }`}>
                        {b.status}
                      </span>
                    </div>

                    <p className="text-xs text-slate-600 font-medium italic">"{b.purpose}"</p>

                    <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-2">
                      <span className="flex items-center gap-1 font-semibold"><Calendar className="w-3.5 h-3.5 text-[#F26522]" /> {b.booking_date}</span>
                      <span className="flex items-center gap-1 font-semibold"><Clock className="w-3.5 h-3.5 text-[#F26522]" /> {b.start_time} - {b.end_time}</span>
                      <span className="text-[#003366] font-bold bg-[#003366]/5 px-2 py-0.5 rounded">{b.club_name}</span>
                    </div>
                  </div>

                  {b.status === 'pending' && user && (parseInt(user.role_id) === 1 || parseInt(user.role_id) === 2) && (
                    <div className="flex items-center gap-2 shrink-0">
                      <button
                        onClick={() => handleApprove(b.id, 'approved')}
                        className="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1 shadow-sm transition-colors"
                      >
                        <Check className="w-4 h-4" /> Approve
                      </button>
                      <button
                        onClick={() => handleApprove(b.id, 'rejected')}
                        className="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-xs flex items-center gap-1 shadow-sm transition-colors"
                      >
                        <X className="w-4 h-4" /> Reject
                      </button>
                    </div>
                  )}
                </div>
              ))
            ) : (
              <div className="p-12 text-center text-slate-500 bg-white border border-slate-200 rounded-2xl shadow-sm">
                No active venue bookings.
              </div>
            )}
          </div>
        </div>

      </div>

    </div>
  );
}
