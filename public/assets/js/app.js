(() => {
  const cardObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
        cardObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.card').forEach((card) => cardObserver.observe(card));

  const menuToggle = document.querySelector('.menu-toggle');
  const siteMenu = document.getElementById('site-menu');

  if (menuToggle && siteMenu) {
    menuToggle.addEventListener('click', () => {
      const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
      document.body.classList.toggle('menu-open', !isExpanded);
    });

    siteMenu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      });
    });
  }

  const passwordToggleBindings = [];

  document.querySelectorAll('.password-toggle').forEach((button) => {
    const targetSelector = button.getAttribute('data-target');
    if (!targetSelector) {
      return;
    }

    const passwordInput = document.querySelector(targetSelector);
    if (!(passwordInput instanceof HTMLInputElement)) {
      return;
    }

    const syncPasswordToggleState = () => {
      const isVisible = passwordInput.type === 'text';
      button.classList.toggle('is-visible', isVisible);
      button.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
      button.setAttribute('aria-label', isVisible ? 'Ocultar senha' : 'Mostrar senha');
      button.setAttribute('title', isVisible ? 'Ocultar senha' : 'Mostrar senha');
    };

    const blinkEye = () => {
      button.classList.remove('is-blinking');
      // Reinicia a animacao para garantir o piscar a cada interacao.
      window.requestAnimationFrame(() => {
        button.classList.add('is-blinking');
      });
      window.setTimeout(() => {
        button.classList.remove('is-blinking');
      }, 220);
    };

    syncPasswordToggleState();

    button.addEventListener('click', () => {
      const isHidingPassword = passwordInput.type === 'text';

      passwordInput.type = isHidingPassword ? 'password' : 'text';
      syncPasswordToggleState();

      if (!isHidingPassword) {
        blinkEye();
      }
    });

    passwordInput.addEventListener('focus', () => {
      if (passwordInput.type === 'password') {
        blinkEye();
      }
    });

    passwordInput.addEventListener('click', () => {
      if (passwordInput.type === 'password') {
        blinkEye();
      }
    });

    passwordToggleBindings.push({ button });
  });

  if (passwordToggleBindings.length > 0) {
    const maxOffset = 3.8;
    const divisor = 28;

    const updateEyeTracking = (clientX, clientY) => {
      passwordToggleBindings.forEach(({ button }) => {
        const rect = button.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const offsetX = Math.max(-maxOffset, Math.min(maxOffset, (clientX - centerX) / divisor));
        const offsetY = Math.max(-maxOffset, Math.min(maxOffset, (clientY - centerY) / divisor));

        button.style.setProperty('--eye-track-x', `${offsetX}px`);
        button.style.setProperty('--eye-track-y', `${offsetY}px`);
      });
    };

    const resetEyeTracking = () => {
      passwordToggleBindings.forEach(({ button }) => {
        button.style.setProperty('--eye-track-x', '0px');
        button.style.setProperty('--eye-track-y', '0px');
      });
    };

    document.addEventListener('pointermove', (event) => {
      updateEyeTracking(event.clientX, event.clientY);
    });

    document.addEventListener('pointerleave', resetEyeTracking);
    window.addEventListener('blur', resetEyeTracking);
  }

  const dismissAlert = (alert) => {
    alert.classList.add('is-hiding');
    alert.addEventListener('transitionend', () => alert.remove(), { once: true });
  };

  document.querySelectorAll('.alert').forEach((alert) => {
    setTimeout(() => dismissAlert(alert), 4000);
  });

  const formatBrazilianPhone = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    if (digits.length <= 2) {
      return digits;
    }

    if (digits.length <= 6) {
      return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    }

    if (digits.length <= 10) {
      return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
    }

    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
  };

  document.querySelectorAll('input[name="telefone"]').forEach((phoneInput) => {
    if (!(phoneInput instanceof HTMLInputElement)) {
      return;
    }

    phoneInput.value = formatBrazilianPhone(phoneInput.value);

    phoneInput.addEventListener('input', () => {
      phoneInput.value = formatBrazilianPhone(phoneInput.value);
    });

    phoneInput.addEventListener('blur', () => {
      phoneInput.value = formatBrazilianPhone(phoneInput.value.trim());
    });
  });



  /* SCRIPT DO CAROUSEL QUE GIRA PARA OS LADOS */

  const carousel = document.getElementById('carousel');
  const items = carousel ? carousel.querySelectorAll('.item') : [];

  if (carousel && items.length > 0) {
    const mobileCarouselQuery = window.matchMedia('(max-width: 767px)');
    const mobileBookingLink = carousel.querySelector('a[data-mobile-redirect="agendamento"]');
    const bookingItemIndex = mobileBookingLink
      ? Array.from(items).findIndex((item) => item.contains(mobileBookingLink))
      : -1;
    let current = bookingItemIndex >= 0 ? bookingItemIndex : Math.min(2, items.length - 1);
    let startY = 0;
    let startX = 0;
    let isTouching = false;
    let isMouseDown = false;

    // Recalcula a posicao lateral e profundidade visual de cada item conforme o item ativo.
    const updateCarousel = () => {
      const isMobileCarousel = mobileCarouselQuery.matches;
      const rotateStep = isMobileCarousel ? 20 : 26;
      const xStep = isMobileCarousel ? 26 : 34;
      const zByOffset = isMobileCarousel ? [250, 170, 70] : [290, 200, 60];
      const scaleStep = isMobileCarousel ? 0.12 : 0.15;
      const opacityStep = isMobileCarousel ? 0.26 : 0.3;

      items.forEach((item, i) => {
        const offset = i - current;
        const absOffset = Math.abs(offset);
        const rotate = offset * rotateStep;
        const translateX = offset * xStep;
        const translate = zByOffset[Math.min(absOffset, zByOffset.length - 1)];
        const scale = 1 - absOffset * scaleStep;
        const opacity = 1 - absOffset * opacityStep;

        item.style.transform = `translateX(${translateX}px) rotateY(${rotate}deg) translateZ(${translate}px) scale(${scale})`;
        item.style.opacity = opacity;
        item.style.zIndex = String(items.length - absOffset);
      });
    };

    // Avanca ou retorna um item quando o gesto horizontal ultrapassa o limite minimo.
    const swipeTo = (deltaX) => {
      if (deltaX > 50 && current < items.length - 1) {
        current += 1;
      } else if (deltaX < -50 && current > 0) {
        current -= 1;
      }

      updateCarousel();
    };

    mobileCarouselQuery.addEventListener('change', updateCarousel);
    window.addEventListener('resize', updateCarousel);

    // Guarda o ponto inicial do toque para detectar a direcao do gesto lateral.
    carousel.addEventListener('touchstart', (event) => {
      const touch = event.touches[0];
      startY = touch.clientY;
      startX = touch.clientX;
      isTouching = true;
    }, { passive: true });

    // Intercepta o gesto horizontal para manter a interacao no carousel.
    carousel.addEventListener('touchmove', (event) => {
      if (!isTouching) {
        return;
      }

      const touch = event.touches[0];
      const deltaY = Math.abs(touch.clientY - startY);
      const deltaX = Math.abs(touch.clientX - startX);

      // Mantem o gesto dentro do carrossel para evitar scroll lateral da pagina.
      if (deltaX > deltaY && deltaX > 8) {
        event.preventDefault();
      }
    }, { passive: false });

    // Finaliza o gesto e aplica a navegacao para esquerda ou direita.
    carousel.addEventListener('touchend', (event) => {
      if (!isTouching) {
        return;
      }

      isTouching = false;
      const endX = event.changedTouches[0].clientX;
      swipeTo(startX - endX);
    }, { passive: true });

    // Cancela o estado de toque quando a interacao eh interrompida.
    carousel.addEventListener('touchcancel', () => {
      isTouching = false;
    }, { passive: true });

    // Marca o inicio do arraste com mouse para reutilizar a mesma logica de swipe.
    carousel.addEventListener('mousedown', (event) => {
      isMouseDown = true;
      startX = event.clientX;
    });

    // Calcula o deslocamento final do mouse e navega entre os itens.
    carousel.addEventListener('mouseup', (event) => {
      if (!isMouseDown) {
        return;
      }

      isMouseDown = false;
      swipeTo(startX - event.clientX);
    });

    // Limpa o estado do arraste se o cursor sair da area do carousel.
    carousel.addEventListener('mouseleave', () => {
      isMouseDown = false;
    });

    const handleMobileCarouselLink = (link) => {
      if (!(link instanceof HTMLAnchorElement)) {
        return;
      }

      link.addEventListener('click', (event) => {
        if (!mobileCarouselQuery.matches) {
          return;
        }

        event.preventDefault();

        const href = link.getAttribute('href') ?? '';
        let destinationUrl;

        try {
          destinationUrl = new URL(href, window.location.origin);
        } catch {
          return;
        }

        // Redireciona apenas para o mesmo dominio para evitar navegacao insegura.
        if (destinationUrl.origin !== window.location.origin) {
          return;
        }

        const card = link.closest('.item');
        if (card) {
          card.classList.add('is-redirecting');
        }

        window.setTimeout(() => {
          window.location.assign(`${destinationUrl.pathname}${destinationUrl.search}${destinationUrl.hash}`);
        }, 550);
      }, { passive: false });
    };

    handleMobileCarouselLink(mobileBookingLink);
    handleMobileCarouselLink(carousel.querySelector('a[data-mobile-redirect="pecas"]'));

    updateCarousel();
  } 

  const profilePhotoInput = document.getElementById('profile-photo-input');
  const profilePreview = document.querySelector('[data-profile-preview]');

  if (profilePhotoInput instanceof HTMLInputElement && profilePreview instanceof HTMLImageElement) {
    profilePhotoInput.addEventListener('change', () => {
      const file = profilePhotoInput.files && profilePhotoInput.files[0] ? profilePhotoInput.files[0] : null;
      if (!file) {
        return;
      }

      const objectUrl = URL.createObjectURL(file);
      profilePreview.src = objectUrl;
      profilePreview.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
    });
  }

  // Modal de cancelamento de agendamento
  const modalCancelar = document.getElementById('modal-cancelar');
  if (modalCancelar) {
    let formPendente = null;

    document.querySelectorAll('.profile-cancelar-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        formPendente = btn.closest('form');
        modalCancelar.classList.add('is-open');
      });
    });

    const btnSim = document.getElementById('modal-cancelar-sim');
    if (btnSim) {
      btnSim.addEventListener('click', function () {
        modalCancelar.classList.remove('is-open');
        if (formPendente) formPendente.submit();
        formPendente = null;
      });
    }

    const btnNao = document.getElementById('modal-cancelar-nao');
    if (btnNao) {
      btnNao.addEventListener('click', function () {
        modalCancelar.classList.remove('is-open');
        formPendente = null;
      });
    }

    modalCancelar.addEventListener('click', function (e) {
      if (e.target === modalCancelar) {
        modalCancelar.classList.remove('is-open');
        formPendente = null;
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalCancelar.classList.contains('is-open')) {
        modalCancelar.classList.remove('is-open');
        formPendente = null;
      }
    });

    // Confirmar remoção de foto
    const deletePhotoForm = document.querySelector('.profile-avatar-delete-form');
    if (deletePhotoForm) {
      deletePhotoForm.addEventListener('submit', function (e) {
        if (!window.confirm('Deseja remover sua foto de perfil?')) {
          e.preventDefault();
        }
      });
    }
  }


  // Tabs de peças originais
  const pecasTabs = document.querySelector('.pecas-tabs');
  if (pecasTabs) {
    pecasTabs.addEventListener('click', (event) => {
      const btn = event.target.closest('.pecas-tab-btn');
      if (!btn) return;

      const tabId = btn.getAttribute('data-tab');
      if (!tabId) return;

      document.querySelectorAll('.pecas-tab-btn').forEach((b) => {
        b.classList.remove('is-active');
        b.setAttribute('aria-selected', 'false');
      });

      document.querySelectorAll('.pecas-painel').forEach((p) => {
        p.classList.remove('is-active');
      });

      btn.classList.add('is-active');
      btn.setAttribute('aria-selected', 'true');

      const painel = document.getElementById('tab-' + tabId);
      if (painel) painel.classList.add('is-active');
    });
  }

})();




