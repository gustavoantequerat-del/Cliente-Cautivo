<script setup>
import BrandMark from '../../Components/BrandMark.vue';
</script>

<template>
    <div :class="$style.wrap">
        <section :class="$style.hero" aria-label="Presentación del portal">
            <BrandMark />
            <div :class="$style.pitch">
                <span :class="$style.kicker">Portal Cliente · Acceso exclusivo</span>
                <h1 :class="$style.title">Su espacio privado en el ecosistema Blockfinity.</h1>
                <p :class="$style.lead">Proyectos, research, formación y documentos para toda su organización, en un solo lugar. Cada funcionario accede con una credencial personal emitida por Blockfinity.</p>
            </div>
            <ul :class="$style.points">
                <li><strong>Credencial personal</strong><span>Nominativa, vinculada a su cargo y revocable por su institución.</span></li>
                <li><strong>Contenido según su contrato</strong><span>Se activan solo las unidades que su entidad tiene contratadas.</span></li>
                <li><strong>Trazabilidad</strong><span>Cada entregable aprobado queda registrado con su huella digital.</span></li>
            </ul>
        </section>
        <section :class="$style.panel">
            <div :class="$style.box"><slot /></div>
        </section>
    </div>
</template>

<style module>
.wrap { min-height: 100vh; display: flex; flex-wrap: wrap; background: var(--bg); }
.hero {
  flex: 1 1 520px; min-width: 0; background: var(--navy-950); color: #fff; padding: 48px 56px;
  display: flex; flex-direction: column; justify-content: space-between; gap: 40px;
}
.pitch { display: flex; flex-direction: column; gap: 20px; max-width: 540px; }
.kicker { font-size: 13px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #9AA6B8; }
.title { font-size: 44px; line-height: 1.08; font-weight: 800; letter-spacing: -0.02em; color: #fff; }
.lead { font-size: 17px; line-height: 1.6; color: var(--side-text); }
.points { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; }
.points li { display: flex; flex-direction: column; gap: 8px; border-top: 1px solid #2A3D5C; padding-top: 14px; }
.points strong { font-size: 15px; }
.points span { font-size: 14px; line-height: 1.5; color: #9AA6B8; }
.panel { flex: 1 1 460px; min-width: 0; padding: 48px 24px; display: flex; align-items: center; justify-content: center; }
.box { width: 100%; max-width: 420px; display: flex; flex-direction: column; gap: 24px; }
@media (max-width: 700px) {
  .hero { padding: 32px 20px; gap: 28px; }
  .title { font-size: 32px; }
  .points { display: none; }
}
</style>
