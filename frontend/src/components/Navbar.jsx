import React, { useState, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { useGSAP } from '@gsap/react';
import gsap from 'gsap';
import { 
  Building2, 
  Terminal, 
  Bot, 
  HeartHandshake, 
  MessageSquare, 
  Droplet, 
  Calendar, 
  LayoutDashboard, 
  LogOut, 
  LogIn, 
  Menu, 
  X,
  Vote,
  ShieldCheck,
  GraduationCap,
  ChevronDown,
  Users
} from 'lucide-react';

export default function Navbar({ activeTab, setActiveTab, onOpenAuth }) {
  const { user, logout } = useAuth();
  const [mobileOpen, setMobileOpen] = useState(false);
  
  // Dropdown states
  const [clubsOpen, setClubsOpen] = useState(false);
  const [communityOpen, setCommunityOpen] = useState(false);
  
  const navRef = useRef(null);

  useGSAP(() => {
    gsap.from(navRef.current, {
      y: -100,
      opacity: 0,
      duration: 1,
      ease: 'power3.out'
    });
  }, { scope: navRef });

  const clubItems = [
    { id: 'computer_club', label: 'Computer Club', icon: Terminal },
    { id: 'robotics_club', label: 'Robotics Club', icon: Bot },
    { id: 'social_service', label: 'Social Service', icon: HeartHandshake },
    { id: 'forum_club', label: 'App Forum', icon: MessageSquare },
  ];

  const communityItems = [
    { id: 'events', label: 'Events & Tickets', icon: Calendar },
    { id: 'forum', label: 'Discussions', icon: MessageSquare },
    { id: 'donors', label: 'Blood Donors', icon: Droplet },
    { id: 'alumni', label: 'Alumni Network', icon: GraduationCap },
  ];

  return (
    <nav ref={navRef} className="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          
          {/* Brand Logo */}
          <div 
            className="flex items-center gap-3 cursor-pointer group shrink-0"
            onClick={() => setActiveTab('home')}
          >
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#003366] to-[#004080] flex items-center justify-center font-bold text-white shadow-md group-hover:scale-105 transition-transform">
              UIU
            </div>
            <div className="hidden sm:block">
              <div className="text-lg font-extrabold tracking-tight text-slate-900 flex items-center gap-1.5 font-serif">
                CMS <span className="text-[#F26522] text-[10px] font-sans font-bold px-1.5 py-0.5 rounded bg-orange-50 border border-orange-200 uppercase tracking-widest shadow-sm">PRO</span>
              </div>
            </div>
          </div>

          {/* Desktop Navigation Links */}
          <div className="hidden lg:flex items-center gap-1 xl:gap-2 relative">
            <button
              onClick={() => setActiveTab('home')}
              className={`px-3 py-2 rounded-lg text-xs xl:text-sm font-bold transition-all ${
                activeTab === 'home'
                  ? 'text-[#F26522] bg-orange-50 border border-orange-100 shadow-sm'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
              }`}
            >
              Home
            </button>

            {/* Clubs Dropdown */}
            <div 
              className="relative"
              onMouseEnter={() => setClubsOpen(true)}
              onMouseLeave={() => setClubsOpen(false)}
            >
              <button
                className={`px-3 py-2 rounded-lg text-xs xl:text-sm font-bold transition-all flex items-center gap-1 ${
                  clubItems.some(c => c.id === activeTab)
                    ? 'text-[#F26522] bg-orange-50 border border-orange-100 shadow-sm'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                }`}
              >
                Clubs <ChevronDown className={`w-3.5 h-3.5 transition-transform ${clubsOpen ? 'rotate-180' : ''}`} />
              </button>
              
              {/* Dropdown Menu */}
              <div className={`absolute top-full left-0 pt-2 w-48 transition-all duration-200 origin-top ${clubsOpen ? 'opacity-100 scale-100 visible' : 'opacity-0 scale-95 invisible'}`}>
                <div className="bg-white rounded-xl shadow-xl border border-slate-100 p-2 space-y-1">
                  {clubItems.map(club => (
                    <button
                      key={club.id}
                      onClick={() => { setActiveTab(club.id); setClubsOpen(false); }}
                      className="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-[#F26522] transition-colors"
                    >
                      <club.icon className="w-4 h-4 text-slate-400 group-hover:text-[#F26522]" />
                      {club.label}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Community Dropdown */}
            <div 
              className="relative"
              onMouseEnter={() => setCommunityOpen(true)}
              onMouseLeave={() => setCommunityOpen(false)}
            >
              <button
                className={`px-3 py-2 rounded-lg text-xs xl:text-sm font-bold transition-all flex items-center gap-1 ${
                  communityItems.some(c => c.id === activeTab)
                    ? 'text-[#F26522] bg-orange-50 border border-orange-100 shadow-sm'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                }`}
              >
                Community <ChevronDown className={`w-3.5 h-3.5 transition-transform ${communityOpen ? 'rotate-180' : ''}`} />
              </button>
              
              {/* Dropdown Menu */}
              <div className={`absolute top-full left-0 pt-2 w-48 transition-all duration-200 origin-top ${communityOpen ? 'opacity-100 scale-100 visible' : 'opacity-0 scale-95 invisible'}`}>
                <div className="bg-white rounded-xl shadow-xl border border-slate-100 p-2 space-y-1">
                  {communityItems.map(item => (
                    <button
                      key={item.id}
                      onClick={() => { setActiveTab(item.id); setCommunityOpen(false); }}
                      className="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-[#F26522] transition-colors"
                    >
                      <item.icon className="w-4 h-4 text-slate-400" />
                      {item.label}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            <button
              onClick={() => setActiveTab('elections')}
              className={`px-3 py-2 rounded-lg text-xs xl:text-sm font-bold transition-all ${
                activeTab === 'elections'
                  ? 'text-[#F26522] bg-orange-50 border border-orange-100 shadow-sm'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
              }`}
            >
              Elections
            </button>

            <button
              onClick={() => setActiveTab('facilities')}
              className={`px-3 py-2 rounded-lg text-xs xl:text-sm font-bold transition-all ${
                activeTab === 'facilities'
                  ? 'text-[#F26522] bg-orange-50 border border-orange-100 shadow-sm'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
              }`}
            >
              Facilities
            </button>
          </div>

          {/* User Auth Section */}
          <div className="hidden lg:flex items-center gap-3 shrink-0">
            {user ? (
              <div className="flex items-center gap-3 pl-4 border-l border-slate-200">
                
                <button
                  onClick={() => setActiveTab('idcard')}
                  className={`p-2 rounded-lg transition-colors border border-transparent ${activeTab === 'idcard' ? 'text-[#F26522] bg-orange-50 border-orange-100' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'}`}
                  title="Digital ID Pass"
                >
                  <ShieldCheck className="w-5 h-5" />
                </button>
                
                <button
                  onClick={() => setActiveTab('dashboard')}
                  className={`p-2 rounded-lg transition-colors border border-transparent ${activeTab === 'dashboard' ? 'text-[#F26522] bg-orange-50 border-orange-100' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'}`}
                  title="Dashboard"
                >
                  <LayoutDashboard className="w-5 h-5" />
                </button>

                <div className="flex items-center gap-2.5 ml-2 pl-3 border-l border-slate-200">
                  <div className="text-right">
                    <div className="text-xs font-bold text-slate-900 leading-tight">{user.name}</div>
                    <div className="text-[10px] text-[#F26522] capitalize font-bold">{user.role}</div>
                  </div>
                  <div className="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-[#003366] shadow-inner">
                    {user.name.charAt(0)}
                  </div>
                </div>

                <button
                  onClick={logout}
                  className="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100 ml-1"
                  title="Logout"
                >
                  <LogOut className="w-4 h-4" />
                </button>
              </div>
            ) : (
              <button
                onClick={onOpenAuth}
                className="uiu-gradient-btn flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white uppercase tracking-wider shadow-md hover:shadow-lg transition-shadow hover:-translate-y-0.5"
              >
                <LogIn className="w-4 h-4" />
                Sign In
              </button>
            )}
          </div>

          {/* Mobile Menu Toggle */}
          <div className="lg:hidden flex items-center">
            <button
              onClick={() => setMobileOpen(!mobileOpen)}
              className="p-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors border border-transparent hover:border-slate-200"
            >
              {mobileOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>

        </div>
      </div>

      {/* Mobile Drawer */}
      {mobileOpen && (
        <div className="lg:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-6 space-y-1.5 shadow-2xl absolute w-full max-h-[calc(100vh-80px)] overflow-y-auto">
          
          <button
            onClick={() => { setActiveTab('home'); setMobileOpen(false); }}
            className={`w-full flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm font-bold transition-colors ${
              activeTab === 'home' ? 'bg-orange-50 text-[#F26522] border border-orange-100 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'
            }`}
          >
            <Building2 className={`w-5 h-5 ${activeTab === 'home' ? 'text-[#F26522]' : 'text-slate-400'}`} />
            Home
          </button>

          <div className="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Clubs</div>
          {clubItems.map((item) => (
            <button
              key={item.id}
              onClick={() => { setActiveTab(item.id); setMobileOpen(false); }}
              className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors ${
                activeTab === item.id ? 'bg-orange-50 text-[#F26522] border border-orange-100 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'
              }`}
            >
              <item.icon className={`w-4 h-4 ${activeTab === item.id ? 'text-[#F26522]' : 'text-slate-400'}`} />
              {item.label}
            </button>
          ))}

          <div className="px-4 py-2 mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Community</div>
          {communityItems.map((item) => (
            <button
              key={item.id}
              onClick={() => { setActiveTab(item.id); setMobileOpen(false); }}
              className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors ${
                activeTab === item.id ? 'bg-orange-50 text-[#F26522] border border-orange-100 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'
              }`}
            >
              <item.icon className={`w-4 h-4 ${activeTab === item.id ? 'text-[#F26522]' : 'text-slate-400'}`} />
              {item.label}
            </button>
          ))}
          
          <div className="pt-4 mt-2 border-t border-slate-100">
            {user ? (
              <div className="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center font-bold text-sm text-[#003366] shadow-sm">
                    {user.name.charAt(0)}
                  </div>
                  <div className="text-left">
                    <div className="text-sm font-bold text-slate-900">{user.name}</div>
                    <div className="text-[11px] text-[#F26522] capitalize font-bold">{user.role}</div>
                  </div>
                </div>
                <div className="flex gap-2">
                  <button onClick={() => { setActiveTab('dashboard'); setMobileOpen(false); }} className="p-2.5 text-slate-500 hover:text-[#F26522] hover:bg-orange-50 rounded-xl transition-colors">
                    <LayoutDashboard className="w-5 h-5" />
                  </button>
                  <button onClick={() => { logout(); setMobileOpen(false); }} className="p-2.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                    <LogOut className="w-5 h-5" />
                  </button>
                </div>
              </div>
            ) : (
              <button
                onClick={() => {
                  onOpenAuth();
                  setMobileOpen(false);
                }}
                className="w-full uiu-gradient-btn py-3.5 rounded-xl text-center text-sm font-bold text-white uppercase tracking-wider shadow-md"
              >
                Sign In / Register
              </button>
            )}
          </div>
        </div>
      )}
    </nav>
  );
}
