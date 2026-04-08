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

    carousel.querySelectorAll('a[data-mobile-redirect]').forEach((link) => {
      handleMobileCarouselLink(link);
    });

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
    const pecasMobileQuery = window.matchMedia('(max-width: 900px)');

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
      btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });

      const painel = document.getElementById('tab-' + tabId);
      if (painel) {
        painel.classList.add('is-active');

        if (pecasMobileQuery.matches) {
          window.requestAnimationFrame(() => {
            const firstCard = painel.querySelector('.peca-card');
            const targetElement = firstCard || painel;
            const topbar = document.querySelector('.topbar');
            const topbarHeight = topbar instanceof HTMLElement ? topbar.offsetHeight : 0;
            const tabsHeight = pecasTabs instanceof HTMLElement ? pecasTabs.offsetHeight : 0;
            const targetTop = window.scrollY + targetElement.getBoundingClientRect().top - topbarHeight - tabsHeight - 12;

            window.scrollTo({
              top: Math.max(0, targetTop),
              behavior: 'smooth',
            });
          });
        }
      }
    });
  }

  // --- Modal Revisão ---
  const pillBtn    = document.getElementById('revisoes-pill-btn');
  const modalOverlay = document.getElementById('revisoes-modal-overlay');
  const modalClose = document.getElementById('revisoes-modal-close');

  if (pillBtn && modalOverlay && modalClose) {
    const openModal = () => {
      modalOverlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      modalClose.focus();
    };

    const closeModal = () => {
      modalOverlay.classList.remove('is-open');
      document.body.style.overflow = '';
      pillBtn.focus();
    };

    pillBtn.addEventListener('click', openModal);
    pillBtn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(); }
    });

    modalClose.addEventListener('click', closeModal);

    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) closeModal();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modalOverlay.classList.contains('is-open')) closeModal();
    });
  }
  // --- /Modal Revisão ---

  // --- Calculadora de Revisões ---
  const calcBtn     = document.getElementById('calc-btn');
  const calcEntrega = document.getElementById('calc-entrega');
  const calcKmInput = document.getElementById('calc-km');
  const calcResults = document.getElementById('calc-results');
  const calcError   = document.getElementById('calc-error');
  const calcCard1   = document.getElementById('calc-card-1');
  const calcCard2   = document.getElementById('calc-card-2');

  if (calcBtn && calcEntrega && calcResults && calcCard1 && calcCard2) {

    // Define hoje como data máxima do input de entrega
    calcEntrega.max = new Date().toISOString().split('T')[0];

    calcBtn.addEventListener('click', () => {
      if (!calcEntrega.value) {
        calcError.textContent = 'Informe a data de entrega da motocicleta.';
        calcError.hidden = false;
        calcResults.hidden = true;
        return;
      }

      calcError.hidden = true;

      const entrega = new Date(calcEntrega.value + 'T00:00:00');
      const hoje    = new Date();
      hoje.setHours(0, 0, 0, 0);
      const kmAtual = calcKmInput && calcKmInput.value !== '' ? parseInt(calcKmInput.value) : null;

      const prazo1 = adicionarMeses(entrega, 6);
      const prazo2 = adicionarMeses(entrega, 12);

      calcCard1.className = 'revisoes-calc-result-card ' + calcStatusClass(prazo1, hoje, 900, 1100, kmAtual);
      calcCard1.innerHTML = calcCardHTML('1ª Revisão Gratuita', prazo1, hoje, 900, 1100, kmAtual);

      calcCard2.className = 'revisoes-calc-result-card ' + calcStatusClass(prazo2, hoje, 5400, 6600, kmAtual);
      calcCard2.innerHTML = calcCardHTML('2ª Revisão Gratuita', prazo2, hoje, 5400, 6600, kmAtual);

      calcResults.hidden = false;
    });
  }

  function adicionarMeses(data, meses) {
    const d = new Date(data);
    d.setMonth(d.getMonth() + meses);
    return d;
  }

  function diffDias(a, b) {
    return Math.round((b - a) / (1000 * 60 * 60 * 24));
  }

  function formatarData(d) {
    return d.toLocaleDateString('pt-BR');
  }

  function calcStatusClass(prazo, hoje, kmMin, kmMax, kmAtual) {
    const dias       = diffDias(hoje, prazo);
    const kmPassou   = kmAtual !== null && kmAtual > kmMax;
    const kmNaFaixa  = kmAtual !== null && kmAtual >= kmMin && kmAtual <= kmMax;
    const prazoVenc  = dias < 0;

    if (prazoVenc || kmPassou) return 'status-danger';
    if (kmNaFaixa || dias <= 30 || (kmAtual !== null && kmAtual >= kmMin - 150)) return 'status-warn';
    return 'status-ok';
  }

  function calcCardHTML(titulo, prazo, hoje, kmMin, kmMax, kmAtual) {
    const dias      = diffDias(hoje, prazo);
    const kmPassou  = kmAtual !== null && kmAtual > kmMax;
    const kmNaFaixa = kmAtual !== null && kmAtual >= kmMin && kmAtual <= kmMax;
    const prazoVenc = dias < 0;

    // Badge de status
    let label, badgeClass;
    if (prazoVenc && kmPassou) {
      label = 'Vencido'; badgeClass = 'danger';
    } else if (prazoVenc) {
      label = 'Prazo vencido'; badgeClass = 'danger';
    } else if (kmPassou) {
      label = 'Km ultrapassado'; badgeClass = 'danger';
    } else if (kmNaFaixa) {
      label = 'Faça agora!'; badgeClass = 'warn';
    } else if (dias <= 30) {
      label = 'Quase no prazo'; badgeClass = 'warn';
    } else {
      label = 'Dentro do prazo'; badgeClass = 'ok';
    }

    // Texto de dias
    let diasTexto;
    if (dias < 0)     diasTexto = 'Venceu há ' + Math.abs(dias) + ' dias';
    else if (dias === 0) diasTexto = 'Vence hoje!';
    else              diasTexto = dias + ' dias restantes';

    // Linha de km atual (se informado)
    let kmLinhaHTML = '';
    if (kmAtual !== null) {
      let kmDesc;
      if (kmAtual < kmMin) {
        kmDesc = 'Faltam ' + (kmMin - kmAtual).toLocaleString('pt-BR') + ' km para o início da faixa';
      } else if (kmAtual <= kmMax) {
        kmDesc = 'Você está na faixa ideal — agende já!';
      } else {
        kmDesc = 'Passou ' + (kmAtual - kmMax).toLocaleString('pt-BR') + ' km do limite';
      }
      kmLinhaHTML = '<div class="revisoes-calc-row">'
        + '<span class="revisoes-calc-row-label">Situação do km</span>'
        + '<span class="revisoes-calc-row-value">' + kmDesc + '</span>'
        + '</div>';
    }

    return '<div class="revisoes-calc-card-title">'
      + '<span>' + titulo + '</span>'
      + '<span class="revisoes-calc-badge ' + badgeClass + '">' + label + '</span>'
      + '</div>'
      + '<div class="revisoes-calc-rows">'
      + '  <div class="revisoes-calc-row">'
      + '    <span class="revisoes-calc-row-label">Prazo limite</span>'
      + '    <span class="revisoes-calc-row-value">' + formatarData(prazo) + '</span>'
      + '  </div>'
      + '  <div class="revisoes-calc-row">'
      + '    <span class="revisoes-calc-row-label">Tempo restante</span>'
      + '    <span class="revisoes-calc-row-value">' + diasTexto + '</span>'
      + '  </div>'
      + '  <div class="revisoes-calc-row">'
      + '    <span class="revisoes-calc-row-label">Faixa de km ideal</span>'
      + '    <span class="revisoes-calc-row-value">' + kmMin.toLocaleString('pt-BR') + ' – ' + kmMax.toLocaleString('pt-BR') + ' km</span>'
      + '  </div>'
      + kmLinhaHTML
      + '</div>';
  }
  // --- /Calculadora de Revisões ---

})();




