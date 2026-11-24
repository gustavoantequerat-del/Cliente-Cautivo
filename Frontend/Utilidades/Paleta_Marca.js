/*
 * Marca de cada institución: a partir de su color principal y de acento arma los tonos que usa
 * el diseño (variables CSS --navy-*, --blue-*, degradados). Se aplican sobre la plantilla del portal,
 * así todo el portal toma los colores de la institución sin tocar cada pantalla.
 */
const Es_Color_Valido = (Color) => typeof Color === 'string' && /^#[0-9a-fA-F]{6}$/.test(Color);
const A_Rgb = (Color) => [1, 3, 5].map((Inicio) => parseInt(Color.slice(Inicio, Inicio + 2), 16));
const A_Hex = (Rgb) => `#${Rgb.map((Canal) => Math.round(Canal).toString(16).padStart(2, '0')).join('')}`;
// Mezcla dos colores: Proporcion 0 = Color_A, 1 = Color_B
const Mezclar = (Color_A, Color_B, Proporcion) => {
    const [A, B] = [A_Rgb(Color_A), A_Rgb(Color_B)];
    return A_Hex(A.map((Canal, Posicion) => Canal + (B[Posicion] - Canal) * Proporcion));
};
const Calcular_Luminosidad = (Color) => {
    const [R, G, B] = A_Rgb(Color).map((Canal) => Canal / 255);
    return 0.2126 * R + 0.7152 * G + 0.0722 * B;
};

export function Construir_Variables_Marca(Color_Primario, Color_Secundario) {
    if (!Es_Color_Valido(Color_Primario)) return {};
    // Un color muy claro (amarillo, celeste) se oscurece para que el texto blanco encima se lea
    const Principal = Calcular_Luminosidad(Color_Primario) > 0.45 ? Mezclar(Color_Primario, '#000000', 0.4) : Color_Primario;
    const Oscuro = Mezclar(Principal, '#000000', 0.62);
    const Medio_Oscuro = Mezclar(Principal, '#000000', 0.5);

    return {
        '--navy-950': Oscuro,
        '--navy-900': Medio_Oscuro,
        '--navy-850': Mezclar(Principal, '#000000', 0.45),
        '--navy-800': Mezclar(Principal, '#000000', 0.3),
        '--navy-700': Principal,
        '--navy-500': Mezclar(Principal, '#FFFFFF', 0.2),
        '--navy-line': Mezclar(Principal, '#000000', 0.4),
        '--blue-100': Mezclar(Principal, '#FFFFFF', 0.88),
        '--blue-50': Mezclar(Principal, '#FFFFFF', 0.94),
        '--side-text': Mezclar(Principal, '#FFFFFF', 0.82),
        '--side-label': Mezclar(Principal, '#FFFFFF', 0.55),
        '--sky-400': Es_Color_Valido(Color_Secundario) ? Color_Secundario : '#5AA9E6',
        '--gradient-primary': `linear-gradient(135deg, ${Principal} 0%, ${Oscuro} 100%)`,
        '--gradient-hero': `linear-gradient(135deg, ${Principal} 0%, ${Medio_Oscuro} 55%, ${Oscuro} 100%)`,
    };
}
