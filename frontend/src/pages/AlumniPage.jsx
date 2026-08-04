import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  GraduationCap, 
  Briefcase, 
  Globe, 
  Mail, 
  MessageSquare, 
  Plus, 
  CheckCircle2,
  X,
  Sparkles
} from 'lucide-react';

export default function AlumniPage({ onOpenAuth }) {
  const { user } = useAuth();
  const [alumni, setAlumni] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedAlumni, setSelectedAlumni] = useState(null);
  const [msg, setMsg] = useState({ type: '', text: '' });
  const containerRef = useRef(null);

  usePageReveal(containerRef, [alumni]);

  // Request form
  const [topic, setTopic] = useState('');
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchAlumni();
  }, []);

  const fetchAlumni = () => {
    setLoading(true);
    api.getAlumni()
      .then(data => {
        if (data.status === 'success') {
          setAlumni(data.alumni || []);
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const handleSendRequest = async (e) => {
    e.preventDefault();
    if (!user) {
      onOpenAuth();
      return;
    }
    const res = await api.requestMentorship(selectedAlumni.id, topic, message);
    if (res.status === 'success') {
      setMsg({ type: 'success', text: 'Mentorship request sent successfully to alumni mentor!' });
      setSelectedAlumni(null);
      setTopic('');
      setMessage('');
    }
  };

  return (
    <div ref={containerRef} className="space-y-10 pb-16">
      
      {/* Title */}
      <div className="text-center max-w-3xl mx-auto space-y-3">
        <div className="gsap-stagger inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-xs font-semibold text-[#F26522] border border-[#F26522]/20 shadow-sm">
          <GraduationCap className="w-4 h-4" /> Tech & Corporate Alumni Directory
        </div>
        <h1 className="text-3xl sm:text-5xl font-extrabold text-slate-900 font-serif">
          UIU Alumni <span className="text-[#F26522]">Mentorship Network</span>
        </h1>
        <p className="gsap-stagger text-sm text-slate-600">
          Connect with former UIU club leaders working at top engineering companies like Brain Station 23, Therap BD, and Google for career guidance.
        </p>
      </div>

      {msg.text && (
        <div className="gsap-stagger max-w-xl mx-auto p-4 rounded-xl text-xs font-bold text-center flex items-center justify-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
          <CheckCircle2 className="w-5 h-5" /> {msg.text}
        </div>
      )}

      {/* Alumni Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {alumni.map((a) => (
          <div key={a.id} className="gsap-card p-6 rounded-3xl bg-white border border-slate-200 shadow-sm hover:shadow-lg space-y-4 flex flex-col justify-between hover:border-[#F26522]/30 transition-all group">
            <div>
              <div className="flex items-center justify-between mb-4">
                <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-slate-50 text-slate-700 border border-slate-200 group-hover:border-[#F26522]/30 group-hover:text-[#F26522] transition-colors">
                  Class of '{a.graduation_year}
                </span>
                <span className="text-[11px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                  {a.current_company}
                </span>
              </div>

              <h3 className="text-lg font-bold text-slate-900 group-hover:text-[#F26522] transition-colors font-serif">{a.name}</h3>
              <p className="text-xs text-[#003366] font-bold uppercase">{a.former_position}</p>

              <div className="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5 text-xs text-slate-700 shadow-inner">
                <div className="flex items-center gap-2">
                  <Briefcase className="w-4 h-4 text-[#F26522]" />
                  <span className="font-bold text-slate-900">{a.current_role}</span>
                </div>
                <div className="text-slate-500 text-[11px] font-medium ml-6">Company: {a.current_company}</div>
              </div>
            </div>

            <button
              onClick={() => {
                if (!user) onOpenAuth();
                else setSelectedAlumni(a);
              }}
              className="w-full mt-5 uiu-gradient-btn py-3 rounded-xl font-bold text-xs text-white uppercase tracking-wider flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-shadow"
            >
              <MessageSquare className="w-4 h-4" /> Book Mentorship
            </button>
          </div>
        ))}
        {alumni.length === 0 && (
          <div className="col-span-3 p-12 text-center text-slate-500 bg-white border border-slate-200 rounded-3xl shadow-sm">
            Loading alumni network...
          </div>
        )}
      </div>

      {/* Mentorship Request Modal */}
      {selectedAlumni && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
          <div className="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 relative shadow-2xl">
            <button
              onClick={() => setSelectedAlumni(null)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-xl font-bold text-slate-900 mb-1 font-serif">Book Session with {selectedAlumni.name}</h3>
            <p className="text-xs text-slate-500 mb-4 font-medium">{selectedAlumni.current_role} at {selectedAlumni.current_company}</p>

            <form onSubmit={handleSendRequest} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Mentorship Topic</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Software Engineering Career Prep & Resume Review"
                  value={topic}
                  onChange={(e) => setTopic(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Your Message / Background</label>
                <textarea
                  rows="4"
                  required
                  placeholder="Briefly introduce yourself and what you hope to learn..."
                  value={message}
                  onChange={(e) => setMessage(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                ></textarea>
              </div>

              <button
                type="submit"
                className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-md mt-2"
              >
                Send Request
              </button>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
