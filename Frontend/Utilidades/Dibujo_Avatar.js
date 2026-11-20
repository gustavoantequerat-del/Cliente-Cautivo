/*
 * Dibuja el avatar como SVG plano (2D) a partir de los objetos que tiene equipados.
 *
 * El cuerpo es un "esqueleto" de articulaciones (hombros, codos, manos, caderas, rodillas, pies).
 * Cada pose solo cambia esas articulaciones, y la ropa se dibuja siguiendo las mismas líneas:
 * por eso cualquier camisa, pantalón u objeto funciona con cualquier pose sin dibujarlo dos veces.
 *
 * Cada objeto es { Tipo, Estilo, Color_Principal, Color_Secundario }. Los estilos válidos están en
 * Backend/app/Modelos/Modelo_Item_Avatar.php (la Forja solo ofrece esos); aquí se ignora cualquier otro.
 */

const Color_Piel = '#F2C9A5';
const Color_Piel_Sombra = '#E0B38D';
const Color_Pelo = '#3B2A20';
const Color_Ropa_Base = '#C9D0D9';
const Color_Contorno = '#1E242C';

const Pose_Estandar = {
    Hombro_Izq: [78, 94], Hombro_Der: [122, 94],
    Codo_Izq: [70, 136], Codo_Der: [130, 136],
    Mano_Izq: [67, 176], Mano_Der: [133, 176],
    Cadera_Izq: [90, 172], Cadera_Der: [110, 172],
    Rodilla_Izq: [88, 216], Rodilla_Der: [112, 216],
    Pie_Izq: [87, 258], Pie_Der: [113, 258],
};

// Cada pose cambia solo las articulaciones que mueve
const Poses = {
    estandar: {},
    saludo: { Codo_Der: [146, 114], Mano_Der: [154, 72] },
    brazos_arriba: { Codo_Izq: [62, 70], Mano_Izq: [54, 34], Codo_Der: [138, 70], Mano_Der: [146, 34] },
    manos_cintura: { Codo_Izq: [56, 130], Mano_Izq: [86, 164], Codo_Der: [144, 130], Mano_Der: [114, 164] },
    pensando: { Codo_Der: [142, 134], Mano_Der: [112, 88] },
    senalando: { Codo_Der: [154, 102], Mano_Der: [186, 98] },
    baile: { Codo_Izq: [58, 72], Mano_Izq: [46, 40], Codo_Der: [150, 126], Mano_Der: [168, 156], Rodilla_Der: [124, 208], Pie_Der: [134, 248] },
};

const Es_Color_Valido = (Color) => typeof Color === 'string' && /^#[0-9a-fA-F]{6}$/.test(Color);
const Punto = ([X, Y]) => `${X},${Y}`;
// Punto intermedio entre dos articulaciones (Fraccion 0 = inicio, 1 = fin)
const Interpolar = ([X1, Y1], [X2, Y2], Fraccion) => [+(X1 + (X2 - X1) * Fraccion).toFixed(1), +(Y1 + (Y2 - Y1) * Fraccion).toFixed(1)];
const Trazo = (Puntos, Color, Grosor) => `<polyline points="${Puntos.map(Punto).join(' ')}" fill="none" stroke="${Color}" stroke-width="${Grosor}" stroke-linecap="round" stroke-linejoin="round"/>`;

// ---------------------------------------------------------------------------
// Partes del cuerpo
// ---------------------------------------------------------------------------
const Dibujar_Torso = (Color) => `<path d="M80,90 Q100,84 120,90 L114,176 L86,176 Z" fill="${Color}" stroke="${Color}" stroke-width="12" stroke-linejoin="round"/>`;
const Dibujar_Brazo = (A, Lado, Color, Grosor = 15) => Trazo([A[`Hombro_${Lado}`], A[`Codo_${Lado}`], A[`Mano_${Lado}`]], Color, Grosor);
const Dibujar_Pierna = (A, Lado, Color, Grosor = 17, Hasta = 1) => {
    const Fin = Hasta < 1 ? Interpolar(A[`Cadera_${Lado}`], A[`Rodilla_${Lado}`], Hasta * 2) : A[`Pie_${Lado}`];
    const Puntos = Hasta <= 0.5 ? [A[`Cadera_${Lado}`], Fin] : [A[`Cadera_${Lado}`], A[`Rodilla_${Lado}`], Fin];
    return Trazo(Puntos, Color, Grosor);
};

function Dibujar_Cabeza() {
    return `<rect x="94" y="70" width="12" height="18" rx="5" fill="${Color_Piel_Sombra}"/>`
        + `<circle cx="100" cy="52" r="27" fill="${Color_Piel}"/>`
        + `<path d="M73,50 Q72,22 100,22 Q128,22 127,50 Q120,34 100,34 Q82,34 73,50 Z" fill="${Color_Pelo}"/>`
        + `<circle cx="90" cy="53" r="3.2" fill="${Color_Contorno}"/><circle cx="110" cy="53" r="3.2" fill="${Color_Contorno}"/>`
        + `<path d="M91,64 Q100,71 109,64" fill="none" stroke="${Color_Contorno}" stroke-width="2.4" stroke-linecap="round"/>`
        + '<circle cx="84" cy="62" r="4" fill="#F4A6A0" opacity="0.5"/><circle cx="116" cy="62" r="4" fill="#F4A6A0" opacity="0.5"/>';
}

// ---------------------------------------------------------------------------
// Ropa y objetos: una función por estilo
// ---------------------------------------------------------------------------
const Dibujantes_Camisa = {
    manga_corta: (A, C1) => Dibujar_Torso(C1)
        + ['Izq', 'Der'].map((L) => Trazo([A[`Hombro_${L}`], Interpolar(A[`Hombro_${L}`], A[`Codo_${L}`], 0.55)], C1, 19)).join(''),
    manga_larga: (A, C1, C2) => Dibujar_Torso(C1)
        + ['Izq', 'Der'].map((L) => Trazo([A[`Hombro_${L}`], A[`Codo_${L}`], Interpolar(A[`Codo_${L}`], A[`Mano_${L}`], 0.8)], C1, 18)
            + `<circle cx="${Interpolar(A[`Codo_${L}`], A[`Mano_${L}`], 0.8).join('" cy="')}" r="9.5" fill="${C2}"/>`).join(''),
    polo: (A, C1, C2) => Dibujar_Torso(C1)
        + ['Izq', 'Der'].map((L) => Trazo([A[`Hombro_${L}`], Interpolar(A[`Hombro_${L}`], A[`Codo_${L}`], 0.5)], C1, 19)).join('')
        + `<path d="M90,86 L100,100 L110,86 Z" fill="${C2}"/><line x1="100" y1="100" x2="100" y2="116" stroke="${C2}" stroke-width="2"/>`,
    chaleco: (A, C1, C2) => Dibujar_Torso(C2)
        + `<path d="M80,90 L96,92 L100,150 L86,176 Z M120,90 L104,92 L100,150 L114,176 Z" fill="${C1}" stroke="${C1}" stroke-width="8" stroke-linejoin="round"/>`,
    saco: (A, C1, C2) => Dibujar_Torso(C1)
        + `<path d="M93,88 L100,112 L107,88 Z" fill="${C2}"/><path d="M93,88 L98,130 L100,112 Z M107,88 L102,130 L100,112 Z" fill="${C1}" stroke="${Color_Contorno}" stroke-opacity="0.25" stroke-width="1.5"/>`
        + `<circle cx="100" cy="140" r="2.5" fill="${C2}"/><circle cx="100" cy="155" r="2.5" fill="${C2}"/>`
        + ['Izq', 'Der'].map((L) => Trazo([A[`Hombro_${L}`], A[`Codo_${L}`], Interpolar(A[`Codo_${L}`], A[`Mano_${L}`], 0.82)], C1, 18)).join(''),
};

const Dibujantes_Pantalon = {
    largo: (A, C1, C2) => ['Izq', 'Der'].map((L) => Dibujar_Pierna(A, L, C1, 20, 0.97)).join('')
        + `<rect x="83" y="160" width="34" height="20" rx="6" fill="${C1}"/><rect x="83" y="160" width="34" height="5" rx="2" fill="${C2}"/>`,
    corto: (A, C1, C2) => ['Izq', 'Der'].map((L) => Dibujar_Pierna(A, L, C1, 21, 0.4)).join('')
        + `<rect x="83" y="160" width="34" height="22" rx="6" fill="${C1}"/><rect x="83" y="160" width="34" height="5" rx="2" fill="${C2}"/>`,
    falda: (A, C1, C2) => `<path d="M84,160 L116,160 L128,214 L72,214 Z" fill="${C1}" stroke="${C1}" stroke-width="6" stroke-linejoin="round"/>`
        + `<rect x="83" y="160" width="34" height="5" rx="2" fill="${C2}"/>`,
};

const Dibujantes_Zapato = {
    zapatilla: ([X, Y], C1, C2) => `<path d="M${X - 11},${Y + 5} Q${X - 12},${Y - 9} ${X},${Y - 8} Q${X + 14},${Y - 6} ${X + 15},${Y + 5} Z" fill="${C1}"/>`
        + `<rect x="${X - 12}" y="${Y + 3}" width="28" height="5" rx="2.5" fill="${C2}"/>`,
    bota: ([X, Y], C1, C2) => `<path d="M${X - 10},${Y + 5} L${X - 10},${Y - 24} L${X + 9},${Y - 24} L${X + 9},${Y - 8} Q${X + 16},${Y - 6} ${X + 16},${Y + 5} Z" fill="${C1}"/>`
        + `<rect x="${X - 11}" y="${Y + 3}" width="28" height="5" rx="2" fill="${C2}"/>`,
    formal: ([X, Y], C1, C2) => `<path d="M${X - 10},${Y + 5} Q${X - 10},${Y - 7} ${X},${Y - 6} Q${X + 14},${Y - 4} ${X + 15},${Y + 5} Z" fill="${C1}"/>`
        + `<path d="M${X - 2},${Y - 3} Q${X + 6},${Y - 4} ${X + 10},${Y}" fill="none" stroke="${C2}" stroke-width="2" stroke-linecap="round"/>`,
};

const Dibujantes_Gorra = {
    gorra: (C1, C2) => `<path d="M72,46 Q72,18 100,18 Q128,18 128,46 Z" fill="${C1}"/><path d="M118,44 Q140,42 146,48 Q132,52 116,50 Z" fill="${C2}"/>`
        + `<circle cx="100" cy="19" r="3" fill="${C2}"/>`,
    sombrero: (C1, C2) => `<ellipse cx="100" cy="36" rx="44" ry="8" fill="${C1}"/><path d="M79,36 L82,10 Q100,4 118,10 L121,36 Z" fill="${C1}"/>`
        + `<rect x="80" y="26" width="40" height="7" fill="${C2}"/>`,
    gorro: (C1, C2) => `<path d="M71,44 Q70,14 100,14 Q130,14 129,44 Z" fill="${C1}"/><rect x="69" y="38" width="62" height="11" rx="5" fill="${C2}"/>`
        + `<circle cx="100" cy="12" r="7" fill="${C2}"/>`,
    corona: (C1, C2) => `<path d="M76,36 L78,12 L89,24 L100,8 L111,24 L122,12 L124,36 Z" fill="${C1}" stroke="${C1}" stroke-width="3" stroke-linejoin="round"/>`
        + `<circle cx="100" cy="28" r="3.5" fill="${C2}"/><circle cx="86" cy="30" r="2.5" fill="${C2}"/><circle cx="114" cy="30" r="2.5" fill="${C2}"/>`,
};

// Objetos que se llevan en la mano: se dibujan centrados en la mano y la mano encima
const Dibujantes_Extra = {
    taza: ([X, Y], C1, C2) => `<path d="M${X + 7},${Y - 8} q9,0 9,7 q0,7 -9,7" fill="none" stroke="${C2}" stroke-width="3"/>`
        + `<rect x="${X - 9}" y="${Y - 12}" width="17" height="21" rx="3" fill="${C1}"/>`,
    laptop: ([X, Y], C1, C2) => `<rect x="${X - 18}" y="${Y - 16}" width="36" height="24" rx="3" fill="${C1}"/><rect x="${X - 14}" y="${Y - 12}" width="28" height="16" rx="2" fill="${C2}"/>`,
    libro: ([X, Y], C1, C2) => `<rect x="${X - 12}" y="${Y - 18}" width="24" height="30" rx="2" fill="${C1}"/><rect x="${X - 12}" y="${Y - 18}" width="5" height="30" fill="${C2}"/>`
        + `<line x1="${X - 3}" y1="${Y - 10}" x2="${X + 8}" y2="${Y - 10}" stroke="${C2}" stroke-width="2"/>`,
    bandera: ([X, Y], C1, C2) => `<line x1="${X}" y1="${Y + 6}" x2="${X}" y2="${Y - 52}" stroke="${C2}" stroke-width="3" stroke-linecap="round"/>`
        + `<path d="M${X + 1},${Y - 52} L${X + 30},${Y - 44} L${X + 1},${Y - 34} Z" fill="${C1}"/>`,
    celular: ([X, Y], C1, C2) => `<rect x="${X - 6}" y="${Y - 16}" width="13" height="22" rx="3" fill="${C1}"/><rect x="${X - 4}" y="${Y - 13}" width="9" height="15" rx="1.5" fill="${C2}"/>`,
    trofeo: ([X, Y], C1, C2) => `<path d="M${X - 10},${Y - 24} L${X + 10},${Y - 24} Q${X + 10},${Y - 6} ${X},${Y - 6} Q${X - 10},${Y - 6} ${X - 10},${Y - 24} Z" fill="${C1}"/>`
        + `<path d="M${X - 10},${Y - 20} q-7,0 -7,6 q0,5 7,5 M${X + 10},${Y - 20} q7,0 7,6 q0,5 -7,5" fill="none" stroke="${C1}" stroke-width="2.5"/>`
        + `<rect x="${X - 2}" y="${Y - 7}" width="4" height="7" fill="${C1}"/><rect x="${X - 8}" y="${Y}" width="16" height="5" rx="1" fill="${C2}"/>`,
};

// Accesorios: algunos van detrás del cuerpo (capa, mochila) y otros encima
const Dibujantes_Accesorio_Detras = {
    capa: (A, C1, C2) => `<path d="M80,90 Q100,84 120,90 L146,238 Q100,250 54,238 Z" fill="${C2}"/><path d="M84,92 Q100,88 116,92 L138,234 Q100,244 62,234 Z" fill="${C1}"/>`,
    mochila: (A, C1) => `<rect x="62" y="90" width="76" height="72" rx="16" fill="${C1}"/>`,
};
const Dibujantes_Accesorio_Encima = {
    capa: (A, C1, C2) => `<circle cx="86" cy="92" r="4" fill="${C2}"/><circle cx="114" cy="92" r="4" fill="${C2}"/>`,
    mochila: (A, C1, C2) => `<path d="M84,92 Q86,120 88,150 M116,92 Q114,120 112,150" fill="none" stroke="${C2}" stroke-width="4" stroke-linecap="round"/>`,
    bufanda: (A, C1, C2) => `<rect x="82" y="78" width="36" height="13" rx="6" fill="${C1}"/><rect x="104" y="86" width="11" height="34" rx="4" fill="${C1}"/>`
        + `<line x1="104" y1="104" x2="115" y2="104" stroke="${C2}" stroke-width="3"/><line x1="104" y1="112" x2="115" y2="112" stroke="${C2}" stroke-width="3"/>`,
    corbata: (A, C1, C2) => `<path d="M96,88 L104,88 L102,96 L98,96 Z" fill="${C2}"/><path d="M98,96 L102,96 L106,138 L100,146 L94,138 Z" fill="${C1}"/>`,
    medalla: (A, C1, C2) => `<path d="M86,90 L100,118 L114,90" fill="none" stroke="${C2}" stroke-width="5" stroke-linejoin="round"/>`
        + `<circle cx="100" cy="124" r="9" fill="${C1}"/><circle cx="100" cy="124" r="4" fill="none" stroke="${C2}" stroke-width="1.5"/>`,
};

/**
 * Devuelve el SVG del avatar con sus objetos equipados.
 * Recorte "busto" muestra solo cabeza y hombros (para la barra superior).
 */
export function Construir_Svg_Avatar(Objetos_Equipados = [], Recorte = 'completo') {
    const Objetos = Objetos_Equipados.map((Objeto) => ({
        ...Objeto,
        C1: Es_Color_Valido(Objeto.Color_Principal) ? Objeto.Color_Principal : '#3B5B8C',
        C2: Es_Color_Valido(Objeto.Color_Secundario) ? Objeto.Color_Secundario : '#FFFFFF',
    }));
    const Buscar = (Tipo) => Objetos.find((Objeto) => Objeto.Tipo === Tipo);
    const Pose = Buscar('pose');
    const A = { ...Pose_Estandar, ...(Poses[Pose?.Estilo] ?? {}) };
    const Camisa = Buscar('camisa');
    const Pantalon = Buscar('pantalon');
    const Zapato = Buscar('zapato');
    const Gorra = Buscar('gorra');
    const Accesorio = Buscar('accesorio');
    const Extras = Objetos.filter((Objeto) => Objeto.Tipo === 'extra').slice(0, 2);

    const Partes = [];
    if (Accesorio && Dibujantes_Accesorio_Detras[Accesorio.Estilo]) Partes.push(Dibujantes_Accesorio_Detras[Accesorio.Estilo](A, Accesorio.C1, Accesorio.C2));
    // Piernas, ropa interior base y pantalón
    Partes.push(['Izq', 'Der'].map((L) => Dibujar_Pierna(A, L, Color_Piel)).join(''));
    Partes.push(`<rect x="84" y="160" width="32" height="26" rx="7" fill="${Color_Ropa_Base}"/>`);
    if (Pantalon && Dibujantes_Pantalon[Pantalon.Estilo]) Partes.push(Dibujantes_Pantalon[Pantalon.Estilo](A, Pantalon.C1, Pantalon.C2));
    Partes.push(['Izq', 'Der'].map((L) => (Zapato && Dibujantes_Zapato[Zapato.Estilo]
        ? Dibujantes_Zapato[Zapato.Estilo](A[`Pie_${L}`], Zapato.C1, Zapato.C2)
        : `<ellipse cx="${A[`Pie_${L}`][0] + 2}" cy="${A[`Pie_${L}`][1] + 1}" rx="10" ry="6" fill="${Color_Piel}"/>`)).join(''));
    // Torso, camisa y brazos
    Partes.push(Dibujar_Torso(Color_Piel));
    Partes.push(['Izq', 'Der'].map((L) => Dibujar_Brazo(A, L, Color_Piel)).join(''));
    if (Camisa && Dibujantes_Camisa[Camisa.Estilo]) Partes.push(Dibujantes_Camisa[Camisa.Estilo](A, Camisa.C1, Camisa.C2));
    if (Accesorio && Dibujantes_Accesorio_Encima[Accesorio.Estilo]) Partes.push(Dibujantes_Accesorio_Encima[Accesorio.Estilo](A, Accesorio.C1, Accesorio.C2));
    // Objetos en las manos (máximo 2: el primero en la mano derecha) y luego las manos encima
    Extras.forEach((Extra, Posicion) => {
        const Mano = A[Posicion === 0 ? 'Mano_Der' : 'Mano_Izq'];
        if (Dibujantes_Extra[Extra.Estilo]) Partes.push(Dibujantes_Extra[Extra.Estilo](Mano, Extra.C1, Extra.C2));
    });
    Partes.push(['Izq', 'Der'].map((L) => `<circle cx="${A[`Mano_${L}`][0]}" cy="${A[`Mano_${L}`][1]}" r="8" fill="${Color_Piel}"/>`).join(''));
    Partes.push(Dibujar_Cabeza());
    if (Gorra && Dibujantes_Gorra[Gorra.Estilo]) Partes.push(Dibujantes_Gorra[Gorra.Estilo](Gorra.C1, Gorra.C2));

    const Caja_Vista = Recorte === 'busto' ? '55 8 90 90' : '0 0 200 280';
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${Caja_Vista}" role="img" aria-label="Avatar">${Partes.join('')}</svg>`;
}
