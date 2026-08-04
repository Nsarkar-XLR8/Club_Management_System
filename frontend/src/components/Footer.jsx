import React from 'react';
import { Building2, Mail, Phone, MapPin, Globe, ShieldCheck, Heart } from 'lucide-react';

export default function Footer({ setActiveTab }) {
  return (
    <footer className="bg-white border-t border-slate-200 text-slate-600 text-xs py-12 mt-20 shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
        
        {/* Col 1 */}
        <div className="space-y-4">
          <div 
            onClick={() => setActiveTab && setActiveTab('home')}
            className="flex items-center gap-2 text-slate-900 font-bold text-base font-serif cursor-pointer hover:opacity-80 transition-opacity"
          >
            <div className="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#003366] to-[#004080] flex items-center justify-center font-bold text-xs text-white">
              UIU
            </div>
            <span>United International University</span>
          </div>
          <p className="text-slate-500 text-xs leading-relaxed">
            The central official digital hub managing UIU Club activities, student registration, event ticket issuance, and faculty budget approvals.
          </p>
        </div>

        {/* Col 2 */}
        <div>
          <h4 className="text-slate-900 font-bold text-sm mb-4 font-serif">Official UIU Clubs</h4>
          <ul className="space-y-2.5">
            <li><button onClick={() => setActiveTab && setActiveTab('computer_club')} className="hover:text-[#F26522] transition-colors text-left w-full">UIU Computer Club (UIUCC)</button></li>
            <li><button onClick={() => setActiveTab && setActiveTab('robotics_club')} className="hover:text-[#F26522] transition-colors text-left w-full">UIU Robotics Club (UIURC)</button></li>
            <li><button onClick={() => setActiveTab && setActiveTab('social_service')} className="hover:text-[#F26522] transition-colors text-left w-full">UIU Social Service Club</button></li>
            <li><button onClick={() => setActiveTab && setActiveTab('forum_club')} className="hover:text-[#F26522] transition-colors text-left w-full">UIU App Forum</button></li>
          </ul>
        </div>

        {/* Col 3 */}
        <div>
          <h4 className="text-slate-900 font-bold text-sm mb-4 font-serif">Campus Location</h4>
          <div className="space-y-2.5 text-xs text-slate-600">
            <div className="flex items-start gap-2">
              <MapPin className="w-4 h-4 text-[#F26522] shrink-0 mt-0.5" />
              <span>United City, Madani Avenue, Badda, Dhaka 1212, Bangladesh</span>
            </div>
            <div className="flex items-center gap-2">
              <Mail className="w-4 h-4 text-[#F26522]" />
              <span>info@uiu.ac.bd</span>
            </div>
            <div className="flex items-center gap-2">
              <Phone className="w-4 h-4 text-[#F26522]" />
              <span>+88 09604-848848</span>
            </div>
          </div>
        </div>

        {/* Col 4 */}
        <div>
          <h4 className="text-slate-900 font-bold text-sm mb-4 font-serif">System Security & Compliance</h4>
          <div className="space-y-3">
            <div className="flex items-center gap-2 text-emerald-700 bg-emerald-50 border border-emerald-100 p-2.5 rounded-lg shadow-sm">
              <ShieldCheck className="w-5 h-5 shrink-0" />
              <span>RBAC Secured Session Management</span>
            </div>
            <p className="text-[11px] text-slate-500">
              Authorized student and faculty access for United International University campus portal.
            </p>
          </div>
        </div>

      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 mt-8 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between text-slate-500 text-[11px]">
        <p>© 2026 United International University Club Management System. All Rights Reserved.</p>
        <p className="flex items-center gap-1 mt-2 md:mt-0">
          Built with <Heart className="w-3.5 h-3.5 text-[#F26522] fill-current" /> for UIU Students
        </p>
      </div>
    </footer>
  );
}
