(function(w) {
  "use strict";

  const DELAY = 2000;
  const DELAY_LONG = 6000;

  const delay = (time) => {
    return (result) => {
      return new Promise(resolve => setTimeout(() => resolve(result), time));
    };
  };

  const router = function(suspect) {
    const suspectNameParts = Array.prototype.filter.call(suspect.classList, function(className) {
      return (className.match(/scene-/) || className.match(/idx\d+/));
    }).join('.');
    if (!suspectNameParts.length) {
      return;
    }

    const match = suspectNameParts.match(/scene-([\w-]+)(\.idx(\d+))?/);
    const suspectName = (match[3] == null) ? match[1] : `${match[1]}.${match[3]}`;
    const scene = suspect.closest('.scene');
    const sceneNum = Array.prototype.find.call(scene.classList, function(className) {
      return className.match(/s\d+/);
    });

    if (sceneHandlers[sceneNum] != null && typeof sceneHandlers[sceneNum] === 'function') {
      sceneHandlers[sceneNum](suspectName, suspect, scene);
    }
  };

  const sceneHandlers = {
    s1: function (suspectName, suspect, scene) {
      switch (suspectName) {
        case 'rails-image.1':
          Promise.resolve()
            .then(delay(DELAY))
            .then(() => suspect.classList.add('centered'))
            .then(delay(DELAY))
            .then(() => suspect.classList.add('boxed'));
          break;

        case 'ruby-code.1':
          Promise.resolve()
            .then(delay(DELAY))
            .then(() => suspect.classList.add('centered'))
            .then(delay(DELAY))
            .then(() => suspect.classList.add('boxed'));
          break;

        case 'ruby-code.2':
          Promise.resolve()
            .then(delay(DELAY))
            .then(() => suspect.classList.add('boxed'));
          break;

        case 'registry':
          Promise.resolve(document.querySelector('.s1 .scene-rails-image.idx2'))
            .then(delay(DELAY_LONG))
            .then((r) => r.classList.add('boxed'));
          break;
      }
    },
  };


  Reveal.addEventListener('fragmentshown', function(ev) {
    router(ev.fragment);
  });
}(window));
