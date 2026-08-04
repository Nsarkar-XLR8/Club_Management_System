import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { QRCodeSVG } from 'qrcode.react';
import jsPDF from 'jspdf';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  Calendar, 
  MapPin, 
  Clock, 
  QrCode, 
  Download, 
  Sparkles, 
  CheckCircle2,
  Ticket,
  Award,
  X
} from 'lucide-react';

export default function EventsPage({ onOpenAuth }) {
  const { user } = useAuth();
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [regSuccess, setRegSuccess] = useState('');
  const containerRef = useRef(null);

  usePageReveal(containerRef, [events]);

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = () => {
    setLoading(true);
    api.getEvents()
      .then(data => {
        if (data.status === 'success') {
          setEvents(data.events);
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const handleRegister = async (eventId) => {
    if (!user) {
      onOpenAuth();
      return;
    }
    const res = await api.registerEvent(eventId);
    if (res.status === 'success') {
      const evt = events.find(e => e.id === eventId);
      setSelectedTicket({
        event: evt,
        qrToken: res.qr_code_token
      });
      setRegSuccess('Event Registration Confirmed!');
    }
  };

  const generatePDFCertificate = (eventTitle) => {
    const doc = new jsPDF({
      orientation: 'landscape',
      unit: 'mm',
      format: 'a4'
    });

    // Light premium UIU theme background
    doc.setFillColor(255, 255, 255);
    doc.rect(0, 0, 297, 210, 'F');

    // UIU Orange Border
    doc.setDrawColor(242, 101, 34);
    doc.setLineWidth(3);
    doc.rect(10, 10, 277, 190);
    
    // Inner border
    doc.setDrawColor(0, 51, 102);
    doc.setLineWidth(0.5);
    doc.rect(14, 14, 269, 182);

    // Title
    doc.setTextColor(242, 101, 34);
    doc.setFontSize(28);
    doc.setFont('helvetica', 'bold');
    doc.text("UNITED INTERNATIONAL UNIVERSITY", 148, 50, { align: "center" });

    doc.setTextColor(0, 51, 102);
    doc.setFontSize(20);
    doc.text("CERTIFICATE OF PARTICIPATION", 148, 75, { align: "center" });

    doc.setFontSize(14);
    doc.setTextColor(100, 116, 139);
    doc.setFont('helvetica', 'normal');
    doc.text("This is to certify that", 148, 95, { align: "center" });

    doc.setFontSize(26);
    doc.setTextColor(15, 23, 42);
    doc.setFont('helvetica', 'bold');
    doc.text(user?.name || "Student Participant", 148, 115, { align: "center" });

    doc.setFontSize(14);
    doc.setTextColor(100, 116, 139);
    doc.setFont('helvetica', 'normal');
    doc.text(`has successfully participated in the campus event:`, 148, 135, { align: "center" });

    doc.setFontSize(18);
    doc.setTextColor(242, 101, 34);
    doc.setFont('helvetica', 'bold');
    doc.text(eventTitle, 148, 150, { align: "center" });

    doc.setFontSize(10);
    doc.setTextColor(148, 163, 184);
    doc.setFont('helvetica', 'normal');
    doc.text(`Verification Token: UIU-CERT-${Math.random().toString(36).substring(2, 9).toUpperCase()}`, 148, 185, { align: "center" });

    doc.save(`UIU_Certificate_${eventTitle.replace(/\s+/g, '_')}.pdf`);
  };

  return (
    <div ref={containerRef} className="space-y-12 pb-16">
      
      {/* Title */}
      <div className="text-center max-w-3xl mx-auto space-y-3">
        <div className="gsap-stagger inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-xs font-semibold text-[#F26522] border border-[#F26522]/20 shadow-sm">
          <Ticket className="w-4 h-4" /> Live QR Ticketing System
        </div>
        <h1 className="text-3xl sm:text-5xl font-extrabold text-slate-900 font-serif">
          UIU Campus <span className="text-[#F26522]">Events & Workshops</span>
        </h1>
        <p className="gsap-stagger text-sm text-slate-600">
          Register for competitive programming hackathons, robotics expos, and charity drives. Get instant digital QR passes.
        </p>
      </div>

      {/* Events Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        {events.length > 0 ? (
          events.map((evt) => (
            <div key={evt.id} className="gsap-card rounded-3xl bg-white border border-slate-200 shadow-md overflow-hidden flex flex-col justify-between hover:border-[#F26522]/30 hover:shadow-xl transition-all">
              <div>
                <div className="h-48 relative overflow-hidden group">
                  <img src={evt.image} alt={evt.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  <div className="absolute top-4 left-4">
                    <span className="px-3 py-1 rounded-full text-xs font-bold bg-white text-[#F26522] border border-slate-200 shadow-sm">
                      {evt.club_name || 'UIU Club'}
                    </span>
                  </div>
                </div>

                <div className="p-6 space-y-4">
                  <h3 className="text-xl font-bold text-slate-900 leading-snug font-serif">{evt.title}</h3>
                  <p className="text-xs text-slate-600 line-clamp-3 leading-relaxed">{evt.description}</p>

                  <div className="grid grid-cols-2 gap-3 text-xs text-slate-700 font-semibold pt-4 border-t border-slate-100">
                    <div className="flex items-center gap-2">
                      <Calendar className="w-4 h-4 text-[#F26522]" />
                      <span>{evt.event_date}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-[#F26522]" />
                      <span>{evt.event_time}</span>
                    </div>
                    <div className="flex items-center gap-2 col-span-2">
                      <MapPin className="w-4 h-4 text-[#F26522]" />
                      <span>{evt.location}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div className="p-6 pt-0 flex gap-3">
                <button
                  onClick={() => handleRegister(evt.id)}
                  className="flex-1 uiu-gradient-btn py-3 rounded-xl text-xs font-bold text-white uppercase tracking-wider flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-shadow"
                >
                  <QrCode className="w-4 h-4" /> Get QR Pass
                </button>

                {user && (
                  <button
                    onClick={() => generatePDFCertificate(evt.title)}
                    className="p-3 rounded-xl bg-slate-50 text-[#003366] hover:bg-[#003366] hover:text-white border border-slate-200 shadow-sm transition-colors"
                    title="Download Certificate PDF"
                  >
                    <Download className="w-5 h-5" />
                  </button>
                )}
              </div>

            </div>
          ))
        ) : (
          <div className="col-span-2 p-12 text-center text-slate-500 bg-white border border-slate-200 rounded-3xl shadow-sm">
            Loading campus events...
          </div>
        )}
      </div>

      {/* QR Ticket Pass Modal */}
      {selectedTicket && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
          <div className="w-full max-w-sm bg-white border border-slate-200 rounded-3xl p-6 text-center relative shadow-2xl space-y-5">
            
            <button
              onClick={() => setSelectedTicket(null)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 shadow-sm">
              <CheckCircle2 className="w-4 h-4" /> Entry Ticket Issued
            </div>

            <div>
              <h3 className="text-base font-bold text-slate-900 font-serif">{selectedTicket.event?.title}</h3>
              <p className="text-xs text-slate-500 mt-1 font-medium">{selectedTicket.event?.location} • {selectedTicket.event?.event_date}</p>
            </div>

            {/* QR Code Container */}
            <div className="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex justify-center items-center mx-auto w-fit">
              <QRCodeSVG value={selectedTicket.qrToken} size={180} />
            </div>

            <div className="text-[11px] font-mono text-[#F26522] bg-orange-50 p-2.5 rounded-xl border border-orange-200 break-all shadow-inner font-bold">
              Token: {selectedTicket.qrToken}
            </div>

            <p className="text-[11px] text-slate-500">
              Show this QR code at UIU campus gate to scan for instant entry.
            </p>

            <button
              onClick={() => generatePDFCertificate(selectedTicket.event?.title || 'UIU Event')}
              className="w-full py-3 rounded-xl bg-slate-900 text-white text-xs font-bold flex items-center justify-center gap-2 hover:bg-[#F26522] transition-colors shadow-md"
            >
              <Download className="w-4 h-4" /> Download Verified Certificate PDF
            </button>

          </div>
        </div>
      )}

    </div>
  );
}
