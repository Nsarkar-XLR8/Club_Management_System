import { useGSAP } from '@gsap/react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export const usePageReveal = (containerRef, dependencies = []) => {
  useGSAP(() => {
    // Select the main elements to animate
    const headers = gsap.utils.toArray(containerRef.current.querySelectorAll('h1, h2'));
    const cards = gsap.utils.toArray(containerRef.current.querySelectorAll('.gsap-card'));
    const staggerElements = gsap.utils.toArray(containerRef.current.querySelectorAll('.gsap-stagger'));

    const tl = gsap.timeline();

    // Animate headers sliding down
    if (headers.length > 0) {
      tl.from(headers, {
        y: -30,
        opacity: 0,
        duration: 0.8,
        stagger: 0.1,
        ease: 'power3.out',
      });
    }

    // Animate stagger elements (like text, buttons) sliding up
    if (staggerElements.length > 0) {
      tl.from(staggerElements, {
        y: 30,
        opacity: 0,
        duration: 0.6,
        stagger: 0.1,
        ease: 'power2.out',
      }, "-=0.4");
    }

    // ScrollTrigger for cards
    cards.forEach((card, i) => {
      gsap.from(card, {
        scrollTrigger: {
          trigger: card,
          start: 'top 85%',
          toggleActions: 'play none none reverse',
        },
        y: 50,
        opacity: 0,
        duration: 0.6,
        ease: 'power2.out',
      });
    });
  }, { scope: containerRef, dependencies: dependencies });
};
