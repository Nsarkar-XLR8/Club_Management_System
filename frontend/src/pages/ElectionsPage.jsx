import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  Vote, 
  UserCheck, 
  Award, 
  CheckCircle2, 
  AlertTriangle, 
  TrendingUp, 
  ShieldCheck, 
  Sparkles,
  Lock
} from 'lucide-react';

export default function ElectionsPage({ onOpenAuth }) {
  const { user } = useAuth();
  const [elections, setElections] = useState([]);
  const [loading, setLoading] = useState(true);
  const [votedMap, setVotedMap] = useState({});
  const [msg, setMsg] = useState({ type: '', text: '' });
  const containerRef = useRef(null);

  usePageReveal(containerRef, [elections]);

  useEffect(() => {
    fetchElections();
  }, []);

  const fetchElections = () => {
    setLoading(true);
    api.getElections()
      .then(data => {
        if (data.status === 'success') {
          setElections(data.elections);
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const handleVote = async (electionId, candidateId) => {
    if (!user) {
      onOpenAuth();
      return;
    }
    setMsg({ type: '', text: '' });
    const res = await api.castVote(electionId, candidateId);
    if (res.status === 'success') {
      setMsg({ type: 'success', text: res.message });
      setVotedMap(prev => ({ ...prev, [electionId]: true }));
      fetchElections();
    } else {
      setMsg({ type: 'error', text: res.message });
    }
  };

  return (
    <div ref={containerRef} className="space-y-10 pb-16">
      
      {/* Title */}
      <div className="text-center max-w-3xl mx-auto space-y-3">
        <div className="gsap-stagger inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-xs font-semibold text-[#F26522] border border-[#F26522]/20 shadow-sm">
          <Vote className="w-4 h-4" /> Secure Digital Ballot System
        </div>
        <h1 className="text-3xl sm:text-5xl font-extrabold text-slate-900 font-serif">
          UIU Executive <span className="text-[#F26522]">Club Elections</span>
        </h1>
        <p className="gsap-stagger text-sm text-slate-600">
          Cast your vote for President, Vice President, and Treasurer. Voting is strictly restricted to verified active members of each club.
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

      {/* Elections Feed */}
      <div className="space-y-12">
        {elections.map((elec) => {
          const totalVotes = elec.candidates?.reduce((acc, c) => acc + parseInt(c.votes_count || 0), 0) || 1;

          return (
            <div key={elec.id} className="gsap-card p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-md space-y-6">
              
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                  <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-orange-50 text-[#F26522] border border-orange-200">
                    {elec.club_name}
                  </span>
                  <h2 className="text-xl font-bold text-slate-900 mt-2 font-serif">{elec.title}</h2>
                  <p className="text-xs text-slate-500 mt-1">{elec.description}</p>
                </div>

                <div className="flex items-center gap-2 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 shadow-sm w-fit">
                  <ShieldCheck className="w-4 h-4" /> Verified Club Members Only
                </div>
              </div>

              {/* Candidates Grid */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {elec.candidates?.map((c) => {
                  const pct = Math.round(((parseInt(c.votes_count || 0)) / totalVotes) * 100);

                  return (
                    <div key={c.id} className="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-4 flex flex-col justify-between hover:border-[#F26522]/30 hover:shadow-md transition-all bg-gradient-to-br from-white to-slate-50">
                      <div className="space-y-3">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-white text-[#003366] flex items-center justify-center font-bold text-sm border border-slate-200 shadow-sm">
                              {c.candidate_name?.charAt(0)}
                            </div>
                            <div>
                              <h3 className="text-base font-bold text-slate-900 font-serif">{c.candidate_name}</h3>
                              <span className="text-xs text-[#F26522] font-bold uppercase">{c.position}</span>
                            </div>
                          </div>

                          <div className="text-right">
                            <span className="text-lg font-extrabold text-[#003366]">{c.votes_count || 0}</span>
                            <span className="text-[10px] text-slate-500 font-medium block">Votes ({pct}%)</span>
                          </div>
                        </div>

                        <p className="text-xs text-slate-600 italic bg-white p-3 rounded-xl border border-slate-200 shadow-sm leading-relaxed">
                          "{c.manifesto}"
                        </p>

                        {/* Progress Bar */}
                        <div className="w-full bg-slate-200 rounded-full h-2 overflow-hidden shadow-inner">
                          <div
                            className="bg-gradient-to-r from-[#003366] to-[#F26522] h-2 rounded-full transition-all duration-700 ease-out"
                            style={{ width: `${pct}%` }}
                          ></div>
                        </div>
                      </div>

                      <button
                        onClick={() => handleVote(elec.id, c.id)}
                        disabled={votedMap[elec.id]}
                        className={`w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 mt-4 transition-all ${
                          votedMap[elec.id]
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-not-allowed shadow-sm'
                            : 'uiu-gradient-btn text-white shadow-md hover:shadow-lg'
                        }`}
                      >
                        {votedMap[elec.id] ? (
                          <>
                            <CheckCircle2 className="w-4 h-4 text-emerald-600" /> Ballot Cast
                          </>
                        ) : (
                          <>
                            <Vote className="w-4 h-4" /> Vote for {c.candidate_name}
                          </>
                        )}
                      </button>
                    </div>
                  );
                })}
              </div>

            </div>
          );
        })}
      </div>

    </div>
  );
}
