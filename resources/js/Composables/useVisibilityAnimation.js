import { ref, onMounted, onBeforeUnmount } from 'vue';

export function useVisibilityAnimation() {
  const visibleSections = ref(new Set());
  let observers = [];

  function setupIntersectionObserver(sectionId, options = {}) {
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 0.3,
      ...options
    };

    onMounted(() => {
      const element = document.getElementById(sectionId);
      if (element) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              visibleSections.value.add(sectionId);
            }
          });
        }, observerOptions);

        observer.observe(element);
        observers.push({ observer, element });
      }
    });

    onBeforeUnmount(() => {
      // Clean up observers when component unmounts
      observers.forEach(({ observer, element }) => {
        observer.unobserve(element);
      });
      observers = [];
    });
  }

  return {
    visibleSections,
    setupIntersectionObserver
  };
}