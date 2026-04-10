<section class="cards-container">

	<a class="card-link" href="<?= e(url('/agendamento')) ?>">
		<div class="card">
			<img src="<?= e(url('/assets/imagens/optimized/agendamentos_q88.jpg')) ?>" alt="Agendamentos" width="960" height="640" loading="lazy" decoding="async">
			<div class="card-content">
				<h3 class="card-title-label">Agendamentos</h3>
			</div>
		</div>
	</a>

	<a class="card-link" href="<?= e(url('/pecas')) ?>">
		<div class="card">
			<img src="<?= e(url('/assets/imagens/optimized/pecas_q88.jpg')) ?>" alt="Peças originais" width="960" height="640" loading="lazy" decoding="async">
			<div class="card-content">
				<h3 class="card-title-label">Peças Originais</h3>
			</div>
		</div>
	</a>

	<a class="card-link" href="<?= e(url('/info-revisoes')) ?>">
		<div class="card">
			<img src="<?= e(url('/assets/imagens/optimized/info-revisao_q88.jpg')) ?>" alt="Informações sobre revisões" width="960" height="640" loading="lazy" decoding="async">
			<div class="card-content">
				<h3 class="card-title-label card-title-label--revisoes">Informações sobre as revisões</h3>
			</div>
		</div>
	</a>
</section>


<!-- CAROUSEL QUE GIRA PARA PARA CIMA E PARA BAIXO -->

<div class="containerk">
  <div class="carousel" id="carousel">
		<div class="item"><a href="<?= e(url('/agendamento')) ?>" data-mobile-redirect="agendamento"><img src="<?= e(url('/assets/imagens/optimized/agen-retrato_q88.jpg')) ?>" alt="Agendamento" width="720" height="1080" loading="lazy" decoding="async"><span class="item-label">Agendamento</span></a></div>
		<div class="item"><a href="<?= e(url('/pecas')) ?>" data-mobile-redirect="pecas"><img src="<?= e(url('/assets/imagens/optimized/pecas-retrato_q88.jpg')) ?>" alt="Peças originais" width="720" height="1080" loading="lazy" decoding="async"><span class="item-label">Peças Originais</span></a></div>
		<div class="item"><a href="<?= e(url('/info-revisoes')) ?>" data-mobile-redirect="info-revisoes"><img src="<?= e(url('/assets/imagens/info-revisao-retrato.jpeg')) ?>" alt="Revisões" width="1024" height="1536" loading="lazy" decoding="async"><span class="item-label item-label--revisoes">Informações sobre as revisões</span></a></div>
	
	<div class="item active"></div>
    <div class="item"></div>
    <div class="item"></div>
  </div>
</div>