<section class="cards-container">

	<a class="card-link" href="<?= e(url('/agendamento')) ?>">
		<div class="card">
			<img src="<?= e(url('/assets/imagens/agendamentos.png')) ?>">
			<div class="card-content">
				<h3 class="card-title-label">Agendamentos</h3>
			</div>
		</div>
	</a>

	<div class="card">
		<img src="<?= e(url('/assets/imagens/pecas.png')) ?>">
		<div class="card-content">
			<h3 class="card-title-label">Peças Originais</h3>
		</div>
	</div>
</section>


<!-- CAROUSEL QUE GIRA PARA PARA CIMA E PARA BAIXO -->

<div class="containerk">
  <div class="carousel" id="carousel">
		<div class="item"><a href="<?= e(url('/agendamento')) ?>" data-mobile-redirect="agendamento"><img src="<?= e(url('/assets/imagens/agen-retrato.png')) ?>"><span class="item-label">Agendamento</span></a></div>
		<div class="item"><img src="<?= e(url('/assets/imagens/pecas-retrato.png')) ?>"><span class="item-label">Peças Originais</span></div>
    
	
	<div class="item active"></div>
    <div class="item"></div>
    <div class="item"></div>
  </div>
</div>