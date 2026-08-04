import React, { useState, useEffect, useRef } from 'react';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  Droplet, 
  Search, 
  Plus, 
  Phone, 
  MapPin, 
  Heart, 
  ShieldCheck,
  X,
  CheckCircle2
} from 'lucide-react';

export default function DonorPage() {
  const [donors, setDonors] = useState([]);
  const [selectedGroup, setSelectedGroup] = useState('');
  const [loading, setLoading] = useState(true);
  const [openRegisterModal, setOpenRegisterModal] = useState(false);
  const [regSuccess, setRegSuccess] = useState('');
  const containerRef = useRef(null);

  usePageReveal(containerRef, [donors, selectedGroup]);

  // Donor form
  const [fullName, setFullName] = useState('');
  const [contact, setContact] = useState('');
  const [bloodGroup, setBloodGroup] = useState('A+');
  const [address, setAddress] = useState('');

  const bloodGroups = ['All', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

  useEffect(() => {
    fetchDonors();
  }, [selectedGroup]);

  const fetchDonors = () => {
    setLoading(true);
    const filter = selectedGroup === 'All' ? '' : selectedGroup;
    api.searchDonors(filter)
      .then(data => {
        if (data.status === 'success') {
          setDonors(data.donors);
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const handleRegisterDonor = async (e) => {
    e.preventDefault();
    const res = await api.registerDonor({
      full_name: fullName,
      contact_number: contact,
      blood_group: bloodGroup,
      permanent_address: address
    });
    if (res.status === 'success') {
      setRegSuccess('Thank you for registering as a blood donor!');
      setOpenRegisterModal(false);
      setFullName('');
      setContact('');
      setAddress('');
      fetchDonors();
    }
  };

  return (
    <div ref={containerRef} className="space-y-8 pb-16">
      
      {/* Header Banner */}
      <div className="gsap-stagger p-8 sm:p-10 rounded-3xl bg-white border border-rose-100 shadow-lg relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div className="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-rose-100 to-transparent rounded-full blur-3xl pointer-events-none"></div>
        
        <div className="relative z-10">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-bold border border-rose-200 mb-3 shadow-sm">
            <Heart className="w-4 h-4" /> UIU Social Service Club Lifesaving Network
          </div>
          <h1 className="text-2xl sm:text-4xl font-extrabold text-slate-900 font-serif">
            Emergency <span className="text-rose-600">Blood Donor Registry</span>
          </h1>
          <p className="text-xs text-slate-600 mt-2 max-w-xl">
            Quickly search verified blood donors among UIU students and faculty members. Every donor can save lives.
          </p>
        </div>

        <button
          onClick={() => setOpenRegisterModal(true)}
          className="relative z-10 px-6 py-3.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-rose-200 hover:shadow-lg transition-all flex items-center gap-2 shrink-0"
        >
          <Plus className="w-4 h-4" /> Register As Blood Donor
        </button>
      </div>

      {regSuccess && (
        <div className="gsap-stagger p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold text-center flex items-center justify-center gap-2 shadow-sm">
          <CheckCircle2 className="w-4 h-4" /> {regSuccess}
        </div>
      )}

      {/* Blood Group Filter Pill Bar */}
      <div className="gsap-stagger flex flex-wrap items-center gap-2">
        {bloodGroups.map((bg) => (
          <button
            key={bg}
            onClick={() => setSelectedGroup(bg)}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm ${
              (selectedGroup === bg || (bg === 'All' && !selectedGroup))
                ? 'bg-rose-600 text-white shadow-rose-200'
                : 'bg-white text-slate-600 border border-slate-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200'
            }`}
          >
            {bg}
          </button>
        ))}
      </div>

      {/* Donors Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {donors.length > 0 ? (
          donors.map((d) => (
            <div key={d.id} className="gsap-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg space-y-4 hover:border-rose-300 transition-all group">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center font-black text-lg shadow-inner group-hover:bg-rose-100 transition-colors">
                    {d.blood_group}
                  </div>
                  <div>
                    <h3 className="text-base font-bold text-slate-900 font-serif">{d.full_name}</h3>
                    <p className="text-xs text-emerald-600 font-bold flex items-center gap-1">
                      <ShieldCheck className="w-3.5 h-3.5" /> Verified UIU Donor
                    </p>
                  </div>
                </div>
              </div>

              <div className="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100 font-medium">
                <div className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-rose-500" />
                  <a href={`tel:${d.contact_number}`} className="font-bold text-slate-900 hover:text-rose-600 hover:underline transition-colors">{d.contact_number}</a>
                </div>
                <div className="flex items-start gap-2">
                  <MapPin className="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                  <span>{d.permanent_address || 'Dhaka, Bangladesh'}</span>
                </div>
              </div>

              <a
                href={`tel:${d.contact_number}`}
                className="w-full py-2.5 rounded-xl bg-slate-50 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 text-rose-600 font-bold text-xs flex items-center justify-center gap-2 transition-colors shadow-sm"
              >
                <Phone className="w-3.5 h-3.5" /> Call Donor Now
              </a>
            </div>
          ))
        ) : (
          <div className="col-span-3 p-12 text-center text-slate-500 bg-white border border-slate-200 rounded-3xl shadow-sm">
            No blood donors found for group {selectedGroup}.
          </div>
        )}
      </div>

      {/* Registration Modal */}
      {openRegisterModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
          <div className="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 relative shadow-2xl">
            <button
              onClick={() => setOpenRegisterModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-xl font-bold text-slate-900 mb-4 font-serif">Register as Blood Donor</h3>

            <form onSubmit={handleRegisterDonor} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Tanvir Hossain"
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Blood Group</label>
                  <select
                    value={bloodGroup}
                    onChange={(e) => setBloodGroup(e.target.value)}
                    className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                  >
                    {['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'].map(b => (
                      <option key={b} value={b}>{b}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                  <input
                    type="text"
                    required
                    placeholder="01700000000"
                    value={contact}
                    onChange={(e) => setContact(e.target.value)}
                    className="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Address / Location</label>
                <input
                  type="text"
                  placeholder="e.g. Dhanmondi, Dhaka"
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                />
              </div>

              <button
                type="submit"
                className="w-full py-3 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 text-white font-bold text-xs uppercase tracking-wider shadow-md mt-2"
              >
                Submit Registry
              </button>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
