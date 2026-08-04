import React, { useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { QRCodeSVG } from 'qrcode.react';
import jsPDF from 'jspdf';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  Building2, 
  Award, 
  ShieldCheck, 
  Download, 
  Sparkles, 
  CheckCircle2,
  QrCode,
  UserCheck
} from 'lucide-react';

export default function IDCardPage({ onOpenAuth }) {
  const { user } = useAuth();
  const containerRef = useRef(null);

  usePageReveal(containerRef, [user]);

  if (!user) {
    return (
      <div ref={containerRef} className="max-w-md mx-auto p-8 rounded-3xl bg-white border border-slate-200 shadow-xl text-center space-y-4">
        <ShieldCheck className="w-16 h-16 text-[#F26522] mx-auto drop-shadow-md" />
        <h3 className="text-2xl font-bold text-slate-900 font-serif">Digital Club ID Pass</h3>
        <p className="text-sm text-slate-500 font-medium">Please sign in to view and download your verified UIU Student Club Digital Pass.</p>
        <button
          onClick={onOpenAuth}
          className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-sm text-white uppercase tracking-wider shadow-md hover:shadow-lg transition-shadow mt-4"
        >
          Sign In Now
        </button>
      </div>
    );
  }

  const exportIDCardPDF = () => {
    const doc = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: [85.6, 53.9] // Standard ID-1 card dimensions
    });

    // Light theme background
    doc.setFillColor(255, 255, 255);
    doc.rect(0, 0, 85.6, 53.9, 'F');

    // UIU Orange Border
    doc.setDrawColor(242, 101, 34);
    doc.setLineWidth(1);
    doc.rect(2, 2, 81.6, 49.9);

    // Header Background
    doc.setFillColor(0, 51, 102);
    doc.rect(2, 2, 81.6, 12, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(7);
    doc.setFont('helvetica', 'bold');
    doc.text("UNITED INTERNATIONAL UNIVERSITY", 42.8, 7, { align: "center" });

    doc.setTextColor(242, 101, 34);
    doc.setFontSize(5);
    doc.text("OFFICIAL STUDENT CLUB PASS", 42.8, 11, { align: "center" });

    // Body
    doc.setFontSize(10);
    doc.setTextColor(15, 23, 42);
    doc.setFont('helvetica', 'bold');
    doc.text(user.name, 42.8, 22, { align: "center" });

    doc.setFontSize(7);
    doc.setTextColor(100, 116, 139);
    doc.setFont('helvetica', 'normal');
    doc.text(`Student ID: ${user.student_id || '011221001'} | Dept: ${user.department || 'CSE'}`, 42.8, 27, { align: "center" });

    doc.setFontSize(8);
    doc.setTextColor(242, 101, 34);
    doc.setFont('helvetica', 'bold');
    doc.text(`Role: ${user.role.toUpperCase()}`, 42.8, 33, { align: "center" });

    // Footer
    doc.setFontSize(5);
    doc.setTextColor(148, 163, 184);
    doc.setFont('helvetica', 'normal');
    doc.text(`Verification Barcode Token: UIU-CARD-${user.id}-2026`, 42.8, 48, { align: "center" });

    doc.save(`UIU_Digital_ID_${user.name.replace(/\s+/g, '_')}.pdf`);
  };

  return (
    <div ref={containerRef} className="space-y-10 pb-16 max-w-xl mx-auto">
      
      {/* Title */}
      <div className="text-center space-y-3">
        <div className="gsap-stagger inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-xs font-semibold text-[#F26522] border border-[#F26522]/20 shadow-sm">
          <ShieldCheck className="w-4 h-4" /> Official Campus Digital Pass
        </div>
        <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif">
          Digital Student <span className="text-[#F26522]">Club ID Card</span>
        </h1>
        <p className="gsap-stagger text-sm text-slate-500 font-medium">
          Verifiable campus digital pass for event gate entries, voting eligibility, and club access.
        </p>
      </div>

      {/* 3D Glassmorphic ID Card (Light Premium) */}
      <div className="gsap-card p-8 rounded-3xl bg-white border border-slate-200 shadow-2xl relative overflow-hidden group hover:shadow-[0_20px_50px_rgba(242,101,34,0.15)] transition-all duration-300">
        <div className="absolute top-0 right-0 w-32 h-32 bg-[#F26522]/10 rounded-full blur-2xl pointer-events-none group-hover:scale-150 transition-transform duration-700"></div>
        <div className="absolute bottom-0 left-0 w-40 h-40 bg-[#003366]/5 rounded-full blur-2xl pointer-events-none group-hover:scale-150 transition-transform duration-700"></div>

        {/* Card Header */}
        <div className="flex items-center justify-between border-b border-slate-100 pb-4 mb-6 relative z-10">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 rounded-xl bg-gradient-to-tr from-[#003366] to-[#004080] flex items-center justify-center font-bold text-white text-sm shadow-md">
              UIU
            </div>
            <div>
              <div className="text-sm font-black text-slate-900 tracking-wider uppercase font-serif">United International University</div>
              <div className="text-[10px] text-[#F26522] font-bold tracking-widest uppercase">Student Club Digital Pass</div>
            </div>
          </div>
          <span className="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
            VERIFIED
          </span>
        </div>

        {/* Card Body */}
        <div className="flex flex-col sm:flex-row items-center gap-6 relative z-10">
          <div className="w-24 h-24 rounded-2xl bg-slate-50 border-2 border-[#F26522] flex items-center justify-center text-4xl font-black text-[#003366] shrink-0 shadow-inner">
            {user.name.charAt(0)}
          </div>

          <div className="space-y-1.5 text-center sm:text-left flex-grow">
            <h2 className="text-2xl font-extrabold text-slate-900 font-serif">{user.name}</h2>
            <div className="text-xs text-slate-500 font-medium">Student ID: <span className="font-mono text-[#F26522] font-bold text-sm bg-orange-50 px-1 rounded">{user.student_id || '011221001'}</span></div>
            <div className="text-xs text-slate-500 font-medium">Department: <span className="font-bold text-slate-700">{user.department || 'CSE'}</span></div>
            <div className="inline-block mt-3 px-3 py-1.5 rounded-lg bg-[#003366] text-white text-[11px] font-bold shadow-md uppercase tracking-wider">
              Role: {user.role}
            </div>
          </div>

          <div className="p-2.5 bg-white rounded-xl shrink-0 shadow-md border border-slate-100">
            <QRCodeSVG value={`UIU-CARD-${user.id}-${user.email}`} size={85} />
          </div>
        </div>

        {/* Card Footer Barcode Token */}
        <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-slate-400 relative z-10">
          <span className="font-bold text-slate-600">Token: UIU-PASS-{user.id}-2026</span>
          <span className="text-emerald-600 font-bold flex items-center gap-1 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200"><CheckCircle2 className="w-3 h-3" /> Gate Scannable</span>
        </div>

      </div>

      {/* Action Button */}
      <button
        onClick={exportIDCardPDF}
        className="gsap-stagger w-full uiu-gradient-btn py-4 rounded-xl font-bold text-xs text-white uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-shadow"
      >
        <Download className="w-5 h-5" /> Download Official Digital ID Card (PDF)
      </button>

    </div>
  );
}
