document.addEventListener('DOMContentLoaded', () => {
  const wraps = Array.from(document.querySelectorAll('.dipi_timeline_item_card-wrap'));
  if (!wraps.length) {
    return;
  }

  const timeline = document.querySelector('.dipi_timeline_container');
  const baseLine = timeline ? timeline.querySelector('.dipi-timeline-line') : null;
  const activeLine = timeline ? timeline.querySelector('.dipi-timeline-line__active') : null;
  const itemContainers = timeline ? Array.from(timeline.querySelectorAll('.dipi_timeline_item_container')) : [];

  let ticking = false;

  const updateTimelineLine = () => {
    if (!timeline || !baseLine || !activeLine || !itemContainers.length) {
      return;
    }

    const timelineRect = timeline.getBoundingClientRect();
    const height = timelineRect.height;
    const pageOffset = window.scrollY || window.pageYOffset || 0;

    const dataPoints = itemContainers.map((item) => {
      const marker = item.querySelector('.ribbon-icon-wrap') || item.querySelector('.dipi_timeline_item_image');
      if (!marker) {
        return null;
      }
      const markerRect = marker.getBoundingClientRect();
      const centerViewport = markerRect.top + markerRect.height / 2;
      return {
        item,
        centerLocal: centerViewport - timelineRect.top,
        centerPage: centerViewport + pageOffset,
      };
    }).filter(Boolean);

    if (!dataPoints.length) {
      return;
    }

    const lineStart = dataPoints[0].centerLocal;
    const lineEnd = dataPoints[dataPoints.length - 1].centerLocal;
    const lineBottom = Math.max(0, height - lineEnd);

    baseLine.style.top = `${lineStart}px`;
    baseLine.style.bottom = `${lineBottom}px`;
    activeLine.style.top = `${lineStart}px`;

    const minPage = dataPoints[0].centerPage;
    const maxPage = dataPoints[dataPoints.length - 1].centerPage;
    const spanPage = Math.max(1, maxPage - minPage);
    const spanLocal = Math.max(1, lineEnd - lineStart);

    const reference = pageOffset + window.innerHeight * 0.35;
    const clamped = Math.min(Math.max(reference, minPage), maxPage);
    const ratio = spanPage === 0 ? 0 : (clamped - minPage) / spanPage;
    const fillOffset = lineStart + ratio * spanLocal;
    const bottom = Math.max(0, height - fillOffset);

    activeLine.style.bottom = `${bottom}px`;

    dataPoints.forEach(({ item, centerLocal }) => {
      if (centerLocal <= fillOffset + 2) {
        item.classList.add('is-filled');
      } else {
        item.classList.remove('is-filled');
      }
    });
  };

  const requestTimelineUpdate = () => {
    if (ticking) {
      return;
    }
    ticking = true;
    requestAnimationFrame(() => {
      ticking = false;
      updateTimelineLine();
    });
  };

  const highlightContainer = (wrap) => {
    const container = wrap.closest('.dipi_timeline_item_container');
    if (container) {
      container.classList.add('is-visible');
    }
  };

  const reveal = (wrap) => {
    wrap.classList.add('is-visible');
    highlightContainer(wrap);
    requestTimelineUpdate();
  };

  const observer = 'IntersectionObserver' in window
    ? new IntersectionObserver(
        (entries, obs) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              obs.unobserve(entry.target);
              reveal(entry.target);
            }
          });
        },
        {
          threshold: 0.3,
          rootMargin: '0px 0px -10% 0px',
        }
      )
    : null;

  wraps.forEach((wrap) => {
    const custom = wrap.closest('.dipi_timeline_item_custom_classes');
    let anim = 'fade';

    if (custom && custom.dataset.config) {
      try {
        const config = JSON.parse(custom.dataset.config);
        anim = config.anim_name || anim;
      } catch (_err) {
        anim = 'fade';
      }
    }

    wrap.classList.add('js-observe');

    if (!observer || anim === 'none') {
      reveal(wrap);
      return;
    }

    observer.observe(wrap);
  });

  updateTimelineLine();
  window.addEventListener('scroll', requestTimelineUpdate);
  window.addEventListener('resize', requestTimelineUpdate);
  window.addEventListener('load', requestTimelineUpdate);
});
