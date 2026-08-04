import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { usePageReveal } from '../hooks/usePageReveal';
import { 
  MessageSquare, 
  ThumbsUp, 
  Plus, 
  MessageCircle, 
  Search, 
  User, 
  Tag,
  X
} from 'lucide-react';

export default function ForumPage({ onOpenAuth }) {
  const { user } = useAuth();
  const [topics, setTopics] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [openModal, setOpenModal] = useState(false);
  const containerRef = useRef(null);

  usePageReveal(containerRef, [topics]);

  // New topic form
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [category, setCategory] = useState('General');

  useEffect(() => {
    fetchTopics();
  }, []);

  const fetchTopics = () => {
    setLoading(true);
    api.getForumTopics()
      .then(data => {
        if (data.status === 'success') {
          setTopics(data.topics);
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  const handleCreateTopic = async (e) => {
    e.preventDefault();
    if (!user) {
      onOpenAuth();
      return;
    }
    const res = await api.createForumTopic({ title, content, category });
    if (res.status === 'success') {
      setOpenModal(false);
      setTitle('');
      setContent('');
      fetchTopics();
    }
  };

  const filteredTopics = topics.filter(t => 
    t.title.toLowerCase().includes(search.toLowerCase()) || 
    t.category.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div ref={containerRef} className="space-y-8 pb-16">
      
      {/* Header */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
          <div className="gsap-stagger inline-flex items-center gap-2 text-xs font-semibold text-[#F26522] mb-1">
            <MessageSquare className="w-4 h-4" /> Community Forum
          </div>
          <h1 className="text-2xl sm:text-4xl font-extrabold text-slate-900 font-serif">UIU App Forum</h1>
          <p className="gsap-stagger text-xs text-slate-500 mt-1">Collaborate on student software projects, tech news, and course discussions.</p>
        </div>

        <button
          onClick={() => {
            if (!user) onOpenAuth();
            else setOpenModal(true);
          }}
          className="gsap-stagger uiu-gradient-btn px-5 py-3 rounded-xl text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2 shadow-md hover:shadow-lg"
        >
          <Plus className="w-4 h-4" /> Start Discussion
        </button>
      </div>

      {/* Search Filter */}
      <div className="gsap-stagger relative max-w-md">
        <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
        <input
          type="text"
          placeholder="Search forum topics..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 shadow-sm rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
        />
      </div>

      {/* Topics Feed */}
      <div className="space-y-4">
        {filteredTopics.length > 0 ? (
          filteredTopics.map((t) => (
            <div key={t.id} className="gsap-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3 hover:shadow-md hover:border-[#F26522]/30 transition-all">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-orange-50 text-[#F26522] border border-orange-200">
                  {t.category}
                </span>
                <span className="text-[11px] text-slate-500 font-medium">{t.created_at}</span>
              </div>

              <h3 className="text-base font-bold text-slate-900 leading-snug font-serif">{t.title}</h3>
              <p className="text-xs text-slate-600 leading-relaxed line-clamp-3">{t.content}</p>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                <div className="flex items-center gap-2">
                  <div className="w-7 h-7 rounded-full bg-slate-100 text-[#003366] flex items-center justify-center font-bold text-[10px] border border-slate-200 shadow-sm">
                    {(t.author_name || 'U').charAt(0)}
                  </div>
                  <span className="text-slate-700 font-bold">{t.author_name || 'UIU Student'}</span>
                </div>

                <div className="flex items-center gap-4">
                  <span className="flex items-center gap-1 hover:text-[#F26522] cursor-pointer transition-colors">
                    <ThumbsUp className="w-4 h-4 text-slate-400" /> {t.upvotes || 0}
                  </span>
                  <span className="flex items-center gap-1">
                    <MessageCircle className="w-4 h-4 text-slate-400" /> {t.comment_count || 0} Comments
                  </span>
                </div>
              </div>
            </div>
          ))
        ) : (
          <div className="p-12 text-center text-slate-500 bg-white border border-slate-200 shadow-sm rounded-3xl">
            No discussion topics found.
          </div>
        )}
      </div>

      {/* New Topic Modal */}
      {openModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
          <div className="w-full max-w-lg bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 relative shadow-2xl">
            <button
              onClick={() => setOpenModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-xl font-bold text-slate-900 mb-4 font-serif">Start New Forum Discussion</h3>

            <form onSubmit={handleCreateTopic} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Topic Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. How to prepare for UIU ICPC preliminary?"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Category</label>
                <select
                  value={category}
                  onChange={(e) => setCategory(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                >
                  <option value="General">General</option>
                  <option value="Competitive Programming">Competitive Programming</option>
                  <option value="Web & Software">Web & Software</option>
                  <option value="Robotics & IoT">Robotics & IoT</option>
                  <option value="Announcements">Announcements</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Content</label>
                <textarea
                  rows="4"
                  required
                  placeholder="Share details or ask a question..."
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522]"
                ></textarea>
              </div>

              <button
                type="submit"
                className="w-full uiu-gradient-btn py-3 rounded-xl font-bold text-sm text-white uppercase tracking-wider shadow-md mt-2"
              >
                Post Discussion
              </button>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
