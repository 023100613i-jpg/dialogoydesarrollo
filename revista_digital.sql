-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-09-2026 a las 18:52:28
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `revista_digital`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alianzas`
--

CREATE TABLE `alianzas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_organizacion` varchar(200) NOT NULL,
  `logo` varchar(500) NOT NULL,
  `url_sitio` varchar(500) DEFAULT NULL,
  `orden` int(11) DEFAULT 1,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alianzas`
--

INSERT INTO `alianzas` (`id`, `nombre_organizacion`, `logo`, `url_sitio`, `orden`, `estado`) VALUES
(1, 'Alianza 1', 'assets/images/logo1.png', '#', 1, 1),
(2, 'Alianza 2', 'assets/images/logo2.png', '#', 2, 1),
(3, 'Alianza 3', 'assets/images/logo3.png', '#', 3, 1),
(4, 'Alianza 4', 'assets/images/logo4.png', '#', 4, 1),
(5, 'Alianza 5', 'assets/images/logo5.png', '#', 5, 1),
(6, 'Alianza 6', 'assets/images/logo6.png', '#', 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `ap_paterno` varchar(100) DEFAULT NULL,
  `ap_materno` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `es_nickname` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id`, `nombres`, `ap_paterno`, `ap_materno`, `nickname`, `es_nickname`) VALUES
(1, 'Juan', 'Pérez', 'García', 'JPerez', 0),
(2, 'Laura', 'Mendoza', 'Rojas', 'LMendoza', 0),
(3, 'Roberto', 'Flores', 'Quispe', 'RFlores', 0),
(4, 'Patricia', 'Rojas', 'Silva', 'PRojas', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletines`
--

CREATE TABLE `boletines` (
  `id` int(10) UNSIGNED NOT NULL,
  `numero_boletin` varchar(50) NOT NULL,
  `resumen` longtext DEFAULT NULL,
  `foto_portada` varchar(255) DEFAULT NULL,
  `archivo_pdf` varchar(255) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `estado` varchar(20) DEFAULT 'publicado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `boletines`
--

INSERT INTO `boletines` (`id`, `numero_boletin`, `resumen`, `foto_portada`, `archivo_pdf`, `fecha_publicacion`, `usuario_id`, `estado`) VALUES
(2, 'NTEP-45', '<p class=\"\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136);\">-Promueven megaproyectos turísticos por S/ 2,400 mllns.</p><p class=\"\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136);\">-Invertirán S/ 9 millones en zonas rurales de Cusco.</p><p class=\"\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136);\">-Producción láctea se duplica en Cajamarca.</p>', 'assets/images/boletin-ntep-45.png', 'assets/pdf/boletin-NTEP-edicion-N45-2808.pdf', '2026-09-10', 1, 'publicado'),
(3, 'NTEP-44', '', 'assets/images/boletin-ntep-44.png', 'assets/pdf/boletin-NTEP-edicion-N44-2508.pdf', '2026-09-10', 1, 'publicado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_remitente` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `asunto` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado_atencion` enum('pendiente','atendido') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especiales`
--

CREATE TABLE `especiales` (
  `id` int(10) UNSIGNED NOT NULL,
  `título` varchar(250) NOT NULL,
  `descripción` text DEFAULT NULL,
  `imagen_portada` varchar(500) NOT NULL,
  `url_destino` varchar(500) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especiales`
--

INSERT INTO `especiales` (`id`, `título`, `descripción`, `imagen_portada`, `url_destino`, `fecha_publicacion`, `usuario_id`, `created_at`) VALUES
(1, 'Por una minería artesanal segura para todos', 'Análisis sobre la minería artesanal y su formalización', 'assets/images/team2.jpg', '#', '2026-09-08', 1, '2026-09-08 17:43:53'),
(2, 'REINFO Días decisivos en el Congreso', 'El futuro del REINFO está en manos del Congreso', 'assets/images/team3.jpg', '#', '2026-09-08', 1, '2026-09-08 17:43:53'),
(3, 'La minería ilegal: un negocio rentable para bandas criminales', 'Investigación sobre el negocio de la minería ilegal', 'assets/images/team4.jpg', '#', '2026-09-08', 1, '2026-09-08 17:43:53'),
(4, 'El problema del REINFO y la minería ilegal en 50 segundos', 'Resumen rápido del problema del REINFO', 'assets/images/team5.jpg', '#', '2026-09-08', 1, '2026-09-08 17:43:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `link_externo` varchar(500) DEFAULT NULL,
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `estado` varchar(20) DEFAULT 'publicado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `foto`, `link_externo`, `fecha_publicacion`, `usuario_id`, `estado`) VALUES
(1, 'Impulsan talento local en Hualgayoc', 'assets/images/nota-facebook-21-11-25.png', 'https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg', '2025-11-21', 1, 'publicado'),
(2, 'Inauguran moderno colegio en Cerro Azul', 'assets/images/nota-facebook-21-11-25b.png', 'https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx', '2025-11-21', 1, 'publicado'),
(3, 'cd', '', 'https://www.dialogoydesarrollo.com.pe/como-evitar-que-el-canon-del-boom-minero-termine-en-obras-de-poco-impacto.html', '2026-09-10', 1, 'archivado'),
(4, 'Más de 730 mineros con Reinfo participan en elecciones', '', 'https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg', '2026-09-10', 1, 'publicado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `podcasts`
--

CREATE TABLE `podcasts` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(500) DEFAULT NULL,
  `url_embed` varchar(500) NOT NULL,
  `duracion` varchar(20) DEFAULT NULL,
  `fecha_publicacion` date NOT NULL,
  `estado` varchar(20) DEFAULT 'publicado',
  `usuario_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `podcasts`
--

INSERT INTO `podcasts` (`id`, `titulo`, `descripcion`, `imagen`, `url_embed`, `duracion`, `fecha_publicacion`, `estado`, `usuario_id`) VALUES
(1, 'RELATOS PARANORMALES con LUAR', 'Historias paranormales', '', 'https://www.youtube.com/live/B6sixTbshIo?si=ES1-Ve5BUH0ckr4w', '1:28:58', '2026-09-12', 'publicado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `redes_sociales`
--

CREATE TABLE `redes_sociales` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_red` varchar(50) NOT NULL,
  `url_perfil` varchar(500) NOT NULL,
  `icono_class` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportajes`
--

CREATE TABLE `reportajes` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `resumen_corto` varchar(500) DEFAULT NULL,
  `desarrollo` longtext NOT NULL,
  `foto_principal` varchar(255) DEFAULT NULL,
  `pdf_adjunto` varchar(255) DEFAULT NULL,
  `fecha_publicacion` date NOT NULL,
  `es_destacado` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('publicado','borrador') DEFAULT 'publicado',
  `autor_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportajes`
--

INSERT INTO `reportajes` (`id`, `titulo`, `resumen_corto`, `desarrollo`, `foto_principal`, `pdf_adjunto`, `fecha_publicacion`, `es_destacado`, `estado`, `autor_id`, `usuario_id`, `created_at`, `updated_at`) VALUES
(6, 'Más de 730 mineros con Reinfo participan en elecciones', '43 candidatos buscan llegar a gobiernos regionales y 692 postulan a alcaldías y regidurías. El analista Iván Arenas advierte los posibles conflictos de interés y el riesgo de que estas autoridades favorezcan las actividades mineras informales.', '<p class=\"\">Un total de 735 candidatos postulan a cargos de gobernador regional, consejero, alcalde y regidor en las elecciones de octubre de 2026 y están inscritos en el Registro Integral de Formalización Minera (Reinfo). Según la base de datos del medio digital <font style=\"background-color: rgb(255, 255, 255);\" color=\"#104a5a\"><b>Territorio Tomado</b></font>, 43 postulan a gobiernos regionales en 15 departamentos, mientras que 692 buscan llegar a alcaldías y regidurías en 22 departamentos. Los partidos con más candidatos con Reinfo son Ahora Nación, Alianza para el Progreso (APP), Perú Primero, Podemos Perú, Progresemos, Somos Perú y Acción Popular.</p><p class=\"\"><br></p><p class=\"\">\r\n</p><p>\r\nEn tanto, la mayoría de los postulantes a gobernador con Reinfo se concentra en Arequipa, con nueve; Madre de Dios, con siete; y Apurímac, con seis, regiones con territorios dominados o con fuerte expansión de la minería ilegal. Son conocidos los centros mineros de Secocha, en la provincia de Camaná, Arequipa; La Pampa, en la provincia de Tambopata, Madre de Dios; y Tapayrihua, en la provincia de Aymaraes, Apurímac.\r\n</p><p>\r\n</p><p><br></p><p>En relación con las elecciones municipales, Apurímac concentra la mayor cantidad de candidatos con Reinfo, con 125 inscritos, seguido de Arequipa, con 98; Puno, con 96; Ayacucho, con 76; La Libertad, con 42; Huancavelica, con 41; Cusco, con 39; y Lima, con 30. En los otros 15 departamentos, la cantidad de aspirantes municipales que cuentan con ese registro oscila entre dos y 26.</p><p><br></p><p>\r\n</p><p style=\"text-align: justify;\">\r\nEn Cusco, Juan de Dios Mayhua Hancco, de Ahora Nación, postula a la alcaldía de Camanti, Quispicanchi, y es representante legal del derecho minero Remy Sebas I con Reinfo suspendido. Yudith Eulalia Quispe Sánchez, de ese mismo partido, postula como alcaldesa para Colquemarca, Chumbivilcas, y es dueña del derecho minero Fuerza de la Esperanza SA 3002 con Reinfo vigente. En ambas provincias se desarrolla minería ilegal e informal cada vez con más intensidad.\r\n</p><p>\r\n</p><p style=\"text-align: justify;\"><br></p><p style=\"text-align: justify;\">Para el analista político Iván Arenas Ramírez, el número de este tipo de candidaturas genera preocupación porque, aunque la formalización de la minería artesanal y de pequeña escala depende del Minem, la fiscalización de las plantas de procesamiento depende de los gobiernos regionales y muchas de ellas operan con Reinfo, lo que podría significar un conflicto de interés entre fiscalizador y fiscalizado.</p><p style=\"text-align: justify;\"><br></p><p>\r\n</p><p style=\"text-align: justify;\">\r\nSegún el analista, el principal problema con las plantas de procesamiento que procesan menos de 350 toneladas diarias es la casi inexistente fiscalización. “Todos sabemos que algunas de estas plantas reciben producción ilegal que se presenta como producción informal”.\r\n</p><p>\r\n</p><p style=\"text-align: justify;\"><br></p><p style=\"text-align: justify;\">Consideró también que existe el riesgo de que, de ganar, estos políticos puedan usar su poder para favorecer la permanencia de actividades mineras no legales. “Ese poder político es amplio y, en algunas regiones, tiene nexos con poderes mediáticos que están al servicio de la minería ilegal o informal”, señaló.</p><p style=\"text-align: justify;\"><br></p><p>\r\n</p><h3 class=\"\" style=\"text-align: justify;\"><b>\r\nEl Reinfo sigue siendo el problema</b></h3><h3 class=\"\" style=\"text-align: justify;\"><b><br></b></h3><h3 class=\"\"><b>\r\n</b></h3><p style=\"text-align: justify; \">\r\nEn ese contexto, ha vuelto a entrar a debate la posibilidad de una nueva ampliación del registro, que debería cerrarse definitivamente en diciembre de este año. La Confederación Nacional de Pequeña Minería y Minería Artesanal del Perú (Confemin) pidió, en su X Congreso Nacional, ampliar el registro por otros tres años, mientras que el ministro de Energía y Minas, Guillermo Shinno, no descartó extenderlo hasta que se apruebe la nueva Ley de la Pequeña Minería y de la Minería Artesanal (Ley MAPE).\r\n</p><p style=\"text-align: justify;\"><br></p><p style=\"text-align: justify;\">\r\nLa eventual ampliación tendrá que ser analizada en el Congreso. La Comisión de Asuntos de Desarrollo Productivo, Energía y Minas, Infraestructura y Trabajo del Senado tiene como integrante al legislador del Partido del Buen Gobierno, Juver Flores Suárez, conocido en Arequipa por haber sido abogado de organizaciones mineras informales. Podría suceder lo que ocurrió en el Congreso anterior, cuando parlamentarios cercanos a los informales presionaron para lograr las sucesivas ampliaciones del Reinfo.</p>', 'assets/images/video.jpg', '', '2026-08-28', 1, 'publicado', 1, 1, '2026-09-05 12:20:00', '2026-09-10 02:09:25'),
(8, 'Cómo evitar que el canon del boom minero termine en obras de poco impacto', 'El nuevo ciclo de altos precios de los minerales volverá a elevar las transferencias a regiones y municipios. El desafío de las autoridades que asumirán en 2027 será usar esos recursos para cerrar brechas y generar desarrollo.', '<p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Hasta julio de este año, los gobiernos regionales recibieron más de 2 500 millones de soles por canon y regalías mineras, mientras que los municipios superaron los 9 000 millones. Todo indica que el superciclo de los precios de los minerales seguirá elevando estas transferencias durante los próximos años.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Las nuevas autoridades regionales y municipales, que asumirán en enero de 2027, administrarán más recursos. El reto será convertirlos en desarrollo sostenible. Luis Miguel Castilla Rubio, exministro de Economía y director ejecutivo de Videnza Instituto, sostiene que estos recursos deben formar parte de una estrategia regional de largo plazo que reduzca desigualdades y disminuya la dependencia de los precios de los minerales.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Para Castilla existe una contradicción evidente entre los ingresos que genera la minería y los beneficios que percibe la población. \"Hay un divorcio muy grande entre los recursos transferidos por canon y regalías y el cierre efectivo de brechas sociales\", afirma.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Explica que la amplia autonomía de los gobiernos subnacionales permite que muchos destinen el canon a proyectos alejados de las necesidades más urgentes. Así, mientras algunos municipios reciben cientos de millones de soles por actividad minera, todavía existen comunidades sin agua potable, alcantarillado, servicios de salud o infraestructura educativa adecuada.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">En cambio, gran parte del presupuesto termina convertido en estadios, plazas, losas deportivas y otras obras de bajo impacto. A eso se suma la atomización del gasto. En lugar de concentrar recursos en proyectos que transformen una localidad, muchas municipalidades realizan pequeñas inversiones que consumen presupuesto sin mejorar las condiciones de vida de la población.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\"><font style=\"color: rgb(0, 0, 0); font-weight: bold;\">El riesgo del clientelismo</font><br>Castilla también cuestiona el uso excesivo de la administración directa. Bajo esta modalidad, los gobiernos locales ejecutan las obras sin procesos de licitación y actúan como empresas constructoras al contratar directamente personal, maquinaria y servicios.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Diversos estudios, señala Castilla, muestran que este mecanismo favorece el empleo temporal con fines políticos, alimenta redes de clientelismo y eleva los riesgos de corrupción. Para el exministro, el país también debe dejar atrás la idea de que el desarrollo depende únicamente del cemento. Carreteras, hospitales y colegios siguen siendo indispensables, pero ya no bastan.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Las inversiones con mejores resultados sociales son las que mejoran la vida y fortalecen las capacidades de la población: agua potable y saneamiento, salud y nutrición infantil, educación de calidad, conectividad vial y digital, electrificación y otras que impulsen la producción regional y generen empleo privado.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Castilla también propone ampliar el uso del canon a proyectos productivos que mejoren la competitividad regional y fortalezcan las cadenas agrícolas, industriales y de servicios, siempre que no generen gastos permanentes para el Estado. Advierte que sería un error utilizar estos recursos para financiar burocracia o crear nuevas plazas laborales. \"Los ingresos volátiles deben financiar inversiones temporales y no gastos permanentes\".</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Según el economista, el nuevo ciclo de ingresos mineros exige revisar la Ley del Canon para que estos recursos ayuden al cierre de brechas y al desarrollo productivo, y fortalecer el planeamiento regional para impedir que cada nueva gestión abandone proyectos estratégicos y priorice obras de corto plazo.</p><p align=\"justify\" class=\"mb-4\" style=\"font-size: 19px; line-height: 30px; color: rgb(136, 136, 136); margin-bottom: 1.5rem !important;\">Castilla sostiene que el próximo quinquenio ofrece una oportunidad difícil de repetir. Si el canon se invierte con visión de largo plazo, se traducirá en mejor educación, salud y desarrollo productivo. Si no, la bonanza minera volverá a dejar miles de millones de soles en obras que no mejoran la vida de la población.</p>', 'assets/images/reportaje-12-08-26.jpg', '', '2026-08-12', 0, 'publicado', 3, 1, '2026-09-05 12:20:00', '2026-09-10 02:07:30'),
(9, 'Minería ilegal: la brecha sigue abierta a una semana del nuevo gobierno', 'La informalidad minera continúa creciendo a pesar de los esfuerzos.', '<p>A una semana del nuevo gobierno, la <strong>minería ilegal</strong> sigue siendo un desafío pendiente.</p>', 'assets/images/reportaje-05-08-26.jpg', '', '2026-08-05', 0, 'publicado', 4, 1, '2026-09-05 12:20:00', '2026-09-09 17:34:09'),
(11, 'Universidades públicas administran casi S/900 millones de canon, regalías y otros recursos determinados', 'Las universidades estatales concentran recursos provenientes de actividades extractivas. Pero han invertido la mitad. Especialistas dicen que una evaluación completa debería ir más allá del porcentaje ejecutado y preguntarse si esas inversiones producen mejores condiciones en formación e investigación.', 'Las más de sesenta universidades públicas del país administran este año, en conjunto, casi S/900 millones provenientes del canon y sobrecanon, regalías, renta de aduanas y participaciones. No todo lo registrado bajo esas categorías es estrictamente “canon”, porque además incluye tanto las transferencias recibidas durante este año como los saldos de balance de años anteriores. Las universidades públicas reciben recursos provenientes de los impuestos y rentas generados por las actividades extractivas después del Gobierno nacional y los gobiernos regionales. Muchas veces, incluso, reciben más transferencias que las municipalidades.', 'assets/images/reportaje-08-09-26.jpg', '', '2026-08-09', 0, 'publicado', 1, 1, '2026-09-09 17:53:02', '2026-09-09 18:56:44'),
(13, 'cd', 'vd', '<p>&nbsp;vd</p>', '', '', '2026-09-10', 0, 'publicado', 3, 1, '2026-09-10 11:55:17', '2026-09-10 11:55:17'),
(14, 'vdw', 'v d', '<p>dc</p>', '', '', '2026-09-10', 0, 'publicado', 2, 1, '2026-09-10 11:55:28', '2026-09-10 11:55:28'),
(15, 'd', 'd', '<p>&nbsp;d</p>', '', '', '2026-09-10', 0, 'publicado', 2, 1, '2026-09-10 11:55:40', '2026-09-10 11:55:40'),
(16, 'd', 'd', '<p>&nbsp;d</p>', '', '', '2026-09-10', 0, 'publicado', 2, 1, '2026-09-10 11:55:57', '2026-09-10 11:55:57'),
(17, 'c dsc', 'd', '<p>&nbsp;d</p>', '', '', '2026-09-10', 0, 'publicado', 2, 1, '2026-09-10 11:56:12', '2026-09-10 11:56:12'),
(18, 'cwewev', 'we', '<p>we</p>', '', '', '2026-09-12', 0, 'publicado', 3, 1, '2026-09-12 15:19:33', '2026-09-12 15:19:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportajes_fotos`
--

CREATE TABLE `reportajes_fotos` (
  `id` int(10) UNSIGNED NOT NULL,
  `reportaje_id` int(10) UNSIGNED NOT NULL,
  `url_foto` varchar(255) NOT NULL,
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportajes_fotos`
--

INSERT INTO `reportajes_fotos` (`id`, `reportaje_id`, `url_foto`, `orden`, `descripcion`) VALUES
(1, 6, 'https://www.dialogoydesarrollo.com.pe/candidatos-con-reinfo.pdf', 0, 'Infografía'),
(2, 8, 'https://www.dialogoydesarrollo.com.pe/la-mineria-forma-genera-recursos.pdf', 0, 'Infografía');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `ap_paterno` varchar(100) NOT NULL,
  `ap_materno` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','editor','redactor') NOT NULL DEFAULT 'redactor',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `ap_paterno`, `ap_materno`, `email`, `password_hash`, `rol`, `created_at`, `reset_token`, `reset_expira`) VALUES
(1, 'José', 'Días', 'Gutierrez', 'admin@ddp.com', '$2y$10$zrOtk/5H51mQg4Dd4QpiD.q65iqQ.YbRKUdrq/7ClFSV94.v5yFKe', 'admin', '2026-09-05 11:44:11', NULL, NULL),
(2, 'María', 'Garcia', 'Torres', 'maria@ddp.com', '$2y$10$K1OyIetuAz..ejlRtQFlXeK6fUrijak9QqK6nl0XydY8lDGZ46DXa', 'editor', '2026-09-05 11:44:11', NULL, NULL),
(3, 'Carlos', 'Ruiz', 'López', 'carlos@ddp.com', '$2y$10$hj1Ovh.AcFVWyGV0zSRiYOUDqMeS6pRN4knWHLBMd711cVNjhE/ka', 'editor', '2026-09-05 11:44:11', NULL, NULL),
(4, 'Ana', 'Torres', 'Mendoza', 'ana@ddp.com', '$2y$10$.BpyKz9P7fVeHfI9PRK5euBY/SUGk0iGgtaKswpIheUFDm7j8G6C2', 'redactor', '2026-09-05 11:44:11', NULL, NULL),
(5, 'Juan', 'Pérez', 'García', 'juan@ddp.com', '$2y$10$.BpyKz9P7fVeHfI9PRK5euBY/SUGk0iGgtaKswpIheUFDm7j8G6C2', 'redactor', '2026-09-05 11:44:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `url_embed` varchar(500) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alianzas`
--
ALTER TABLE `alianzas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletines`
--
ALTER TABLE `boletines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_boletines_numero` (`numero_boletin`),
  ADD KEY `fk_boletines_usuario` (`usuario_id`),
  ADD KEY `idx_boletines_fecha` (`fecha_publicacion`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `especiales`
--
ALTER TABLE `especiales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_especiales_usuario` (`usuario_id`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_noticias_usuario` (`usuario_id`),
  ADD KEY `idx_noticias_fecha` (`fecha_publicacion`);

--
-- Indices de la tabla `podcasts`
--
ALTER TABLE `podcasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_podcasts_usuario` (`usuario_id`),
  ADD KEY `idx_podcasts_fecha` (`fecha_publicacion`);

--
-- Indices de la tabla `redes_sociales`
--
ALTER TABLE `redes_sociales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reportajes`
--
ALTER TABLE `reportajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reportajes_autor` (`autor_id`),
  ADD KEY `fk_reportajes_usuario` (`usuario_id`),
  ADD KEY `idx_reportajes_fecha` (`fecha_publicacion`),
  ADD KEY `idx_reportajes_destacado` (`es_destacado`);

--
-- Indices de la tabla `reportajes_fotos`
--
ALTER TABLE `reportajes_fotos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reportajes_fotos_reportaje` (`reportaje_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_videos_usuario` (`usuario_id`),
  ADD KEY `idx_videos_fecha` (`fecha_publicacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alianzas`
--
ALTER TABLE `alianzas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `boletines`
--
ALTER TABLE `boletines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `especiales`
--
ALTER TABLE `especiales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `podcasts`
--
ALTER TABLE `podcasts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `redes_sociales`
--
ALTER TABLE `redes_sociales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportajes`
--
ALTER TABLE `reportajes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `reportajes_fotos`
--
ALTER TABLE `reportajes_fotos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `boletines`
--
ALTER TABLE `boletines`
  ADD CONSTRAINT `fk_boletines_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `especiales`
--
ALTER TABLE `especiales`
  ADD CONSTRAINT `fk_especiales_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `podcasts`
--
ALTER TABLE `podcasts`
  ADD CONSTRAINT `fk_podcasts_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportajes`
--
ALTER TABLE `reportajes`
  ADD CONSTRAINT `fk_reportajes_autor` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reportajes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportajes_fotos`
--
ALTER TABLE `reportajes_fotos`
  ADD CONSTRAINT `fk_reportajes_fotos_reportaje` FOREIGN KEY (`reportaje_id`) REFERENCES `reportajes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_videos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
