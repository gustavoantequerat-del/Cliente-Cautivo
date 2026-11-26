<script setup>
import Componente_Marca_Blockfinity from '../Componentes/Componente_Marca_Blockfinity.vue';
import Componente_Icono from '../Componentes/Componente_Icono.vue';

const Beneficios = [
    { Icono: 'credencial', Texto: 'Credencial personal' },
    { Icono: 'capas', Texto: 'El ecosistema Blockfinity' },
    { Icono: 'escudo', Texto: 'Documentos verificables' },
];
</script>

<template>
    <div :class="$style.wrap">
        <section :class="$style.hero" aria-label="Presentación del portal">
            <span :class="[$style.orb, $style.orbA]" aria-hidden="true" />
            <span :class="[$style.orb, $style.orbB]" aria-hidden="true" />
            <Componente_Marca_Blockfinity />
            <div :class="$style.pitch" class="animar-entrada">
                <span :class="$style.kicker">Portal Cliente</span>
                <h1 :class="$style.title">Todo su trabajo con Blockfinity, en un solo lugar.</h1>
            </div>
            <ul :class="$style.points" class="escalonado">
                <li v-for="Beneficio in Beneficios" :key="Beneficio.Texto">
                    <span class="burbuja burbuja-oscura"><Componente_Icono :Nombre="Beneficio.Icono" :Tamano="20" /></span>
                    <strong>{{ Beneficio.Texto }}</strong>
                </li>
            </ul>
        </section>
        <section :class="$style.panel">
            <div :class="$style.box" class="animar-entrada"><slot /></div>
        </section>
    </div>
</template>

<style module>
.wrap { min-height: 100vh; display: flex; flex-wrap: wrap; background: var(--bg); }
.hero {
  position: relative; overflow: hidden; flex: 1 1 520px; min-width: 0; background: var(--gradient-hero); color: #fff; padding: 48px 56px;
  display: flex; flex-direction: column; justify-content: space-between; gap: 40px;
}
.orb { position: absolute; border-radius: 50%; filter: blur(2px); pointer-events: none; animation: flotar 9s ease-in-out infinite; }
.orbA { width: 420px; height: 420px; right: -140px; top: -120px; background: radial-gradient(circle at 30% 30%, rgba(90, 169, 230, 0.45), transparent 70%); }
.orbB { width: 300px; height: 300px; left: -100px; bottom: -80px; background: radial-gradient(circle at 60% 40%, rgba(59, 91, 140, 0.7), transparent 70%); animation-delay: -4s; }
.pitch { position: relative; display: flex; flex-direction: column; gap: 16px; max-width: 520px; }
.kicker { align-self: flex-start; font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #CFE0F5; background: rgba(255, 255, 255, 0.1); padding: 6px 12px; border-radius: 999px; }
.title { font-size: 44px; line-height: 1.08; font-weight: 800; letter-spacing: -0.02em; color: #fff; }
.points { position: relative; list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 12px; }
.points li {
  display: flex; align-items: center; gap: 12px; padding: 10px 16px 10px 10px; border-radius: 16px;
  background: rgba(255, 255, 255, 0.07); border: 1px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(6px);
}
.points strong { font-size: 14px; }
.panel { flex: 1 1 460px; min-width: 0; padding: 48px 24px; display: flex; align-items: center; justify-content: center; }
.box { width: 100%; max-width: 400px; display: flex; flex-direction: column; gap: 24px; }
@media (max-width: 700px) {
  .hero { padding: 32px 20px; gap: 24px; }
  .title { font-size: 30px; }
  .points { display: none; }
}
</style>
