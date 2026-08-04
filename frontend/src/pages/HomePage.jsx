import React, { useRef } from 'react';
import { 
  Building2, 
  Terminal, 
  Bot, 
  HeartHandshake, 
  MessageSquare, 
  Calendar, 
  Users, 
  ArrowRight, 
  Sparkles,
  CheckCircle2,
  Zap,
  TrendingUp
} from 'lucide-react';
import { usePageReveal } from '../hooks/usePageReveal';

export default function HomePage({ setActiveTab, onOpenAuth }) {
  const containerRef = useRef(null);
  usePageReveal(containerRef); // Apply GSAP animations

  const clubs = [
    {
      id: 'computer_club',
      code: 'UIUCC',
      name: 'UIU Computer Club',
      motto: 'Innovate, Code, Elevate',
      desc: 'The official tech club of United International University organizing competitive programming, ICPC prep, hackathons, and web dev bootcamps.',
      icon: Terminal,
      color: 'from-orange-500 to-amber-500',
      badge: 'Tech & Software',
      members: '650+ Active Members'
    },
    {
      id: 'robotics_club',
      code: 'UIURC',
      name: 'UIU Robotics Club',
      motto: 'Building the Future with Robotics',
      desc: 'Focusing on IoT engineering, autonomous Mars rovers, drone design, and national robotics competitions.',
      icon: Bot,
      color: 'from-[#003366] to-blue-700',
      badge: 'Hardware & IoT',
      members: '420+ Innovators'
    },
    {
      id: 'social_service',
      code: 'UIUSSC',
      name: 'UIU Social Service Club',
      motto: 'Serving Humanity with Passion',
      desc: 'Dedicated to emergency blood donation drives, winter warm distribution, flood relief, and community welfare.',
      icon: HeartHandshake,
      color: 'from-rose-500 to-red-500',
      badge: 'Blood & Welfare',
      members: '800+ Volunteers'
    },
    {
      id: 'forum_club',
      code: 'UIUAF',
      name: 'UIU App Forum',
      motto: 'Connect, Share, Empower',
      desc: 'The centralized campus forum for software showcases, career mentoring, codebase discussions, and project partner finding.',
      icon: MessageSquare,
      color: 'from-emerald-500 to-teal-500',
      badge: 'Community & Projects',
      members: '1,200+ Discussions'
    }
  ];

  return (
    <div ref={containerRef} className="space-y-20 pb-16">
      
      {/* HERO SECTION */}
      <section className="relative overflow-hidden pt-12 pb-20">
        <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#F26522]/15 rounded-full blur-[140px] pointer-events-none animate-blob"></div>
        <div className="absolute top-1/3 right-1/4 w-[400px] h-[400px] bg-[#003366]/10 rounded-full blur-[100px] pointer-events-none animate-blob" style={{ animationDelay: '2s' }}></div>
        <div className="absolute bottom-10 left-10 w-[300px] h-[300px] bg-emerald-500/10 rounded-full blur-[80px] pointer-events-none animate-blob" style={{ animationDelay: '4s' }}></div>
        
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
          
          <div className="gsap-stagger inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white text-xs font-semibold text-[#F26522] border border-[#F26522]/20 shadow-sm mb-8 animate-bounce hover:scale-105 transition-transform cursor-default">
            <Sparkles className="w-4 h-4" /> Official Campus Portal for United International University
          </div>

          <h1 className="text-4xl sm:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight max-w-4xl mx-auto font-serif">
            Empowering Campus Clubs & Student Leadership at <span className="uiu-gradient-text">UIU</span>
          </h1>

          <p className="gsap-stagger mt-6 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
            Discover active university clubs, register for campus hackathons & workshops, track blood donation networks, and scan QR event tickets effortlessly.
          </p>

          <div className="gsap-stagger mt-10 flex flex-wrap justify-center items-center gap-4">
            <button
              onClick={() => setActiveTab('events')}
              className="uiu-gradient-btn px-7 py-3.5 rounded-xl text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 shadow-lg hover:shadow-xl hover:scale-105 transition-all"
            >
              Explore Events & QR Tickets <ArrowRight className="w-4 h-4" />
            </button>

            <button
              onClick={() => {
                const el = document.getElementById('clubs-section');
                if (el) el.scrollIntoView({ behavior: 'smooth' });
              }}
              className="px-7 py-3.5 rounded-xl bg-white text-sm font-semibold text-slate-700 hover:text-[#F26522] hover:bg-orange-50 border border-slate-300 hover:border-orange-200 transition-all shadow-sm hover:shadow-md hover:scale-105 flex items-center gap-2"
            >
              Browse UIU Clubs
            </button>
          </div>

          {/* Quick Stats Grid */}
          <div className="gsap-stagger mt-16 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-5xl mx-auto">
            {[
              { label: 'Active Campus Clubs', value: '4 Major Bodies', icon: Building2 },
              { label: 'Registered Members', value: '2,500+ Students', icon: Users },
              { label: 'Events & Workshops', value: '120+ Hosted', icon: Calendar },
              { label: 'Blood Donations', value: '850+ Donors', icon: HeartHandshake },
            ].map((stat, idx) => {
              const Icon = stat.icon;
              return (
                <div key={idx} className="group p-5 rounded-2xl bg-white border border-slate-200 shadow-sm text-left hover:border-[#F26522]/40 hover:shadow-lg transition-all hover:-translate-y-1">
                  <Icon className="w-6 h-6 text-[#F26522] mb-2 group-hover:scale-110 transition-transform" />
                  <div className="text-lg font-bold text-slate-900 group-hover:text-[#003366] transition-colors">{stat.value}</div>
                  <div className="text-xs text-slate-500 font-medium">{stat.label}</div>
                </div>
              );
            })}
          </div>

        </div>
      </section>

      {/* CLUBS SHOWCASE SECTION */}
      <section id="clubs-section" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="text-2xl sm:text-4xl font-extrabold text-slate-900 font-serif">
            Explore <span className="text-[#F26522]">UIU Executive Clubs</span>
          </h2>
          <p className="text-sm text-slate-500 mt-2 max-w-lg mx-auto">
            Select a club to view upcoming events, member rosters, executive board announcements, and budget logs.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {clubs.map((club) => {
            const Icon = club.icon;
            return (
              <div
                key={club.id}
                onClick={() => setActiveTab(club.id)}
                className="gsap-card group p-6 rounded-2xl bg-white border border-slate-200 hover:border-[#F26522]/40 shadow-sm hover:shadow-xl hover:shadow-[#F26522]/5 transition-all duration-300 hover:-translate-y-1.5 cursor-pointer flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <div className={`w-12 h-12 rounded-xl bg-gradient-to-tr ${club.color} flex items-center justify-center text-white shadow-md group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300`}>
                      <Icon className="w-6 h-6" />
                    </div>
                    <span className="text-[10px] font-bold tracking-wider px-2.5 py-1 rounded-full bg-slate-50 text-slate-700 border border-slate-200 group-hover:border-[#F26522]/30 group-hover:text-[#F26522] transition-colors">
                      {club.code}
                    </span>
                  </div>

                  <h3 className="text-lg font-bold text-slate-900 group-hover:text-[#F26522] transition-colors font-serif">
                    {club.name}
                  </h3>
                  <p className="text-xs font-semibold text-[#003366] italic mt-0.5 mb-3">
                    "{club.motto}"
                  </p>
                  <p className="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-6">
                    {club.desc}
                  </p>
                </div>

                <div className="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                  <span className="text-slate-500 font-medium">{club.members}</span>
                  <span className="text-[#F26522] font-bold group-hover:translate-x-1 transition-transform flex items-center gap-1">
                    Visit Page <ArrowRight className="w-3.5 h-3.5" />
                  </span>
                </div>
              </div>
            );
          })}
        </div>
      </section>

      {/* ENTERPRISE FEATURES HIGHLIGHT */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="gsap-card p-8 sm:p-12 rounded-3xl bg-white border border-slate-200 shadow-xl relative overflow-hidden group">
          {/* Decorative Background Blob */}
          <div className="absolute top-0 right-0 w-80 h-80 bg-gradient-to-bl from-[#F26522]/10 via-[#003366]/5 to-transparent rounded-full blur-3xl pointer-events-none group-hover:scale-110 transition-transform duration-1000"></div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center relative z-10">
            
            <div className="space-y-6">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200 shadow-sm">
                <Zap className="w-4 h-4" /> Next-Gen Enterprise Upgrades
              </div>
              <h2 className="text-2xl sm:text-4xl font-extrabold text-slate-900 leading-tight font-serif">
                Modern QR Attendance, PDF Verification & Budget Governance
              </h2>
              <p className="text-sm text-slate-600 leading-relaxed">
                The upgraded UIU CMS introduces instant digital pass generation for event registrations, seamless camera scanner validation for campus gate entry, and automated PDF certificate issuance.
              </p>

              <div className="space-y-3">
                {[
                  'Automated QR Code Ticket Passes for UIU Workshops & Contests',
                  'Instant Live Gate Attendance Verification via Mobile QR Scanner',
                  'Verified Digital PDF Certificates with Unique Verification Tokens',
                  'Faculty Advisor Budget Approval & Sponsorship Financial Workflow'
                ].map((feat, i) => (
                  <div key={i} className="flex items-center gap-3 text-xs sm:text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                    <CheckCircle2 className="w-5 h-5 text-[#F26522] shrink-0" />
                    <span>{feat}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-4 shadow-inner">
              <div className="flex items-center justify-between pb-3 border-b border-slate-200">
                <span className="text-xs font-bold text-slate-800 uppercase tracking-wider">Live System Preview</span>
                <span className="text-[10px] text-emerald-700 font-bold px-2 py-0.5 rounded bg-emerald-100 border border-emerald-200 animate-pulse">ONLINE</span>
              </div>

              <div className="p-4 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-between hover:border-[#F26522]/30 hover:shadow-md transition-all group/card cursor-pointer" onClick={() => setActiveTab('events')}>
                <div>
                  <div className="text-xs font-bold text-slate-900 group-hover/card:text-[#F26522] transition-colors">UIU Inter-University Hackathon 2026</div>
                  <div className="text-[11px] text-slate-500 font-mono mt-0.5">QR Ticket Token: UIU-EVT-1-USR-5-a8f3</div>
                </div>
                <button
                  className="px-3 py-1.5 rounded-lg bg-gradient-to-r from-[#003366] to-[#004080] text-white text-xs font-bold shadow-md group-hover/card:scale-105 transition-transform"
                >
                  View Pass
                </button>
              </div>

              <div className="p-4 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-between hover:border-emerald-300 hover:shadow-md transition-all cursor-default">
                <div>
                  <div className="text-xs font-bold text-slate-900">Faculty Budget Approval</div>
                  <div className="text-[11px] text-emerald-600 font-bold mt-0.5">Requested: ৳50,000 | Status: Approved</div>
                </div>
                <div className="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center border border-emerald-100">
                  <TrendingUp className="w-4 h-4 text-emerald-500" />
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>

    </div>
  );
}
