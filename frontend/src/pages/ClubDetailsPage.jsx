import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  Terminal, 
  Bot, 
  HeartHandshake, 
  MessageSquare, 
  Users, 
  Calendar, 
  UserPlus, 
  CheckCircle2,
  Award,
  Sparkles,
  MapPin,
  Clock
} from 'lucide-react';

export default function ClubDetailsPage({ clubId, onOpenAuth }) {
  const { user } = useAuth();
  const [club, setClub] = useState(null);
  const [loading, setLoading] = useState(true);
  const [joined, setJoined] = useState(false);
  const containerRef = useRef(null);

  usePageReveal(containerRef, [club, clubId]);

  const clubInfoMap = {
    computer_club: {
      id: 1,
      name: 'UIU Computer Club',
      code: 'UIUCC',
      icon: Terminal,
      color: 'from-orange-500 to-amber-600',
      banner: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1200',
      motto: 'Innovate, Code, Elevate',
      desc: 'UIU Computer Club is dedicated to cultivating top programming talent, hackathon champions, and competitive software developers at United International University.'
    },
    robotics_club: {
      id: 2,
      name: 'UIU Robotics Club',
      code: 'UIURC',
      icon: Bot,
      color: 'from-blue-500 to-indigo-600',
      banner: 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=1200',
      motto: 'Building the Future with Robotics',
      desc: 'UIU Robotics Club provides hands-on hardware labs, IoT workshops, and competitive Mars Rover engineering projects.'
    },
    social_service: {
      id: 3,
      name: 'UIU Social Service Club',
      code: 'UIUSSC',
      icon: HeartHandshake,
      color: 'from-rose-500 to-red-600',
      banner: 'https://images.unsplash.com/photo-1593113598332-cd288d649433?w=1200',
      motto: 'Serving Humanity with Passion',
      desc: 'The official social service wing of UIU organizing blood donation drives, winter clothes distribution, and emergency charity campaigns.'
    },
    forum_club: {
      id: 4,
      name: 'UIU App Forum',
      code: 'UIUAF',
      icon: MessageSquare,
      color: 'from-emerald-500 to-teal-600',
      banner: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200',
      motto: 'Connect, Share, Empower',
      desc: 'The central student forum for project collaborations, app showcases, tech discussions, and academic peer networking.'
    }
  };

  const meta = clubInfoMap[clubId] || clubInfoMap.computer_club;
  const Icon = meta.icon;

  useEffect(() => {
    setLoading(true);
    api.getClubDetails(meta.id)
      .then(data => {
        if (data.status === 'success') {
          setClub(data.club);
          if (user && data.club.members) {
            const isMember = data.club.members.some(m => m.id === user.id);
            setJoined(isMember);
          }
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [clubId, user]);

  const handleJoin = async () => {
    if (!user) {
      onOpenAuth();
      return;
    }
    const res = await api.joinClub(meta.id);
    if (res.status === 'success') {
      setJoined(true);
    }
  };

  return (
    <div ref={containerRef} className="space-y-12 pb-16">
      
      {/* Banner */}
      <div className="gsap-stagger relative rounded-3xl overflow-hidden bg-white border border-slate-200 shadow-xl">
        <div className="h-64 sm:h-80 w-full relative">
          <img src={meta.banner} alt={meta.name} className="w-full h-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-t from-white via-white/40 to-transparent"></div>
        </div>

        <div className="p-6 sm:p-10 -mt-24 relative z-10 flex flex-col md:flex-row items-start md:items-end justify-between gap-6">
          <div className="flex items-center gap-5">
            <div className={`w-24 h-24 rounded-2xl bg-gradient-to-tr ${meta.color} flex items-center justify-center text-white shadow-2xl shrink-0 border-4 border-white`}>
              <Icon className="w-12 h-12" />
            </div>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif">{meta.name}</h1>
                <span className="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 shadow-sm mt-1">
                  {meta.code}
                </span>
              </div>
              <p className="text-sm font-semibold text-[#F26522] italic mt-1 font-mono">
                "{meta.motto}"
              </p>
            </div>
          </div>

          <button
            onClick={handleJoin}
            disabled={joined}
            className={`px-8 py-4 rounded-xl font-bold text-sm uppercase tracking-wider flex items-center gap-2 transition-all shadow-md ${
              joined
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                : 'uiu-gradient-btn text-white hover:shadow-lg'
            }`}
          >
            {joined ? (
              <>
                <CheckCircle2 className="w-5 h-5" /> Registered Member
              </>
            ) : (
              <>
                <UserPlus className="w-5 h-5" /> Join {meta.code}
              </>
            )}
          </button>
        </div>
      </div>

      {/* Overview & Description */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div className="lg:col-span-2 space-y-8">
          <div className="gsap-card p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <h3 className="text-xl font-bold text-slate-900 mb-4 font-serif">About {meta.name}</h3>
            <p className="text-sm text-slate-600 leading-relaxed font-medium">
              {meta.desc}
            </p>

            <div className="grid grid-cols-2 sm:grid-cols-3 gap-6 mt-8 pt-8 border-t border-slate-100">
              <div className="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div className="text-xs text-slate-500 font-bold mb-1 uppercase tracking-wider">Total Members</div>
                <div className="text-xl font-black text-slate-900">{club?.members?.length || 45} Students</div>
              </div>
              <div className="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div className="text-xs text-slate-500 font-bold mb-1 uppercase tracking-wider">Established</div>
                <div className="text-xl font-black text-slate-900">2014</div>
              </div>
              <div className="bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                <div className="text-xs text-emerald-700 font-bold mb-1 uppercase tracking-wider">Status</div>
                <div className="text-lg font-black text-emerald-600 flex items-center gap-1">
                  <Sparkles className="w-5 h-5" /> Active Club
                </div>
              </div>
            </div>
          </div>

          {/* Members Roster */}
          <div className="gsap-card p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <h3 className="text-xl font-bold text-slate-900 mb-6 flex items-center justify-between font-serif">
              <span>Executive Board & Roster</span>
              <Users className="w-6 h-6 text-[#F26522]" />
            </h3>

            <div className="space-y-3">
              {club?.members && club.members.length > 0 ? (
                club.members.map((m, idx) => (
                  <div key={idx} className="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between hover:bg-white hover:border-slate-200 hover:shadow-sm transition-all">
                    <div className="flex items-center gap-4">
                      <div className="w-10 h-10 rounded-full bg-white text-[#003366] flex items-center justify-center font-bold text-sm border border-slate-200 shadow-sm">
                        {m.name.charAt(0)}
                      </div>
                      <div>
                        <div className="text-sm font-bold text-slate-900">{m.name}</div>
                        <div className="text-xs text-slate-500 font-medium">{m.email} {m.student_id ? `(${m.student_id})` : ''}</div>
                      </div>
                    </div>
                    <span className="text-xs font-bold px-3 py-1.5 rounded-lg bg-white text-[#003366] border border-slate-200 shadow-sm uppercase tracking-wider">
                      {m.position || 'Member'}
                    </span>
                  </div>
                ))
              ) : (
                [
                  { name: 'Computer Club Executive', email: 'exec.cc@uiu.ac.bd', position: 'President' },
                  { name: 'Dr. Salekul Islam', email: 'advisor@cse.uiu.ac.bd', position: 'Faculty Advisor' },
                  { name: 'General Student User', email: 'student@bscse.uiu.ac.bd', position: 'Member' }
                ].map((m, idx) => (
                  <div key={idx} className="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between hover:bg-white hover:border-slate-200 hover:shadow-sm transition-all">
                    <div className="flex items-center gap-4">
                      <div className="w-10 h-10 rounded-full bg-white text-[#F26522] flex items-center justify-center font-bold text-sm border border-slate-200 shadow-sm">
                        {m.name.charAt(0)}
                      </div>
                      <div>
                        <div className="text-sm font-bold text-slate-900">{m.name}</div>
                        <div className="text-xs text-slate-500 font-medium">{m.email}</div>
                      </div>
                    </div>
                    <span className="text-[10px] font-bold px-3 py-1.5 rounded-lg bg-orange-50 text-[#F26522] border border-orange-200 shadow-sm uppercase tracking-widest">
                      {m.position}
                    </span>
                  </div>
                ))
              )}
            </div>
          </div>

        </div>

        {/* Sidebar Info */}
        <div className="space-y-6">
          <div className="gsap-stagger p-6 rounded-3xl bg-[#003366] border border-blue-900 shadow-xl space-y-5 relative overflow-hidden">
            <div className="absolute top-0 right-0 w-32 h-32 bg-[#F26522]/20 rounded-full blur-2xl pointer-events-none"></div>
            
            <h4 className="text-lg font-bold text-white border-b border-blue-800 pb-4 font-serif relative z-10">Club Information</h4>
            
            <div className="space-y-4 text-xs text-blue-100 font-medium relative z-10">
              <div className="flex items-start gap-3 bg-blue-900/40 p-3 rounded-xl border border-blue-800/50">
                <MapPin className="w-5 h-5 text-[#F26522] shrink-0" />
                <span>UIU Campus, Madani Avenue, Badda, Dhaka</span>
              </div>
              <div className="flex items-start gap-3 bg-blue-900/40 p-3 rounded-xl border border-blue-800/50">
                <Clock className="w-5 h-5 text-[#F26522] shrink-0" />
                <span>Weekly Meetings: Tuesdays at 4:00 PM</span>
              </div>
              <div className="flex items-start gap-3 bg-blue-900/40 p-3 rounded-xl border border-blue-800/50">
                <Award className="w-5 h-5 text-[#F26522] shrink-0" />
                <span>Certified UIU Student Body Organization</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  );
}
