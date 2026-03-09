# Tiempo21 de Radio Victoria - Tema WordPress

## Configuración

Ve a **Apariencia → Personalizar** y configura:

| Sección | Qué hacer |
|---------|-----------|
| **Identidad del sitio** | Sube el logo banner (imagen ancha, max 90px alto) |
| **Redes Sociales** | Ingresa las URLs de Facebook, Twitter, Instagram, YouTube, Telegram |
| **Radio / Audio en Vivo** | URL del stream MP3/M3U8 de Radio Victoria |
| **Sección de Imágenes / Links** | 4 imágenes con sus URLs y textos |
| **Últimas / Más leídas** | Cantidad de noticias a mostrar |
| **Secciones de Categorías (Inicio)** | ID de cada categoría y cantidad de noticias |
| **Sección de Videos** | Título y URL de YouTube de 3 videos |
| **Información del Footer** | Texto de copyright |

### Menús
Ir a **Apariencia → Menús**:
- Crear menú para ubicación **"Menú Principal"**
- Crear menú para ubicación **"Menú Footer"**

### Noticia Principal (Hero)
Para mostrar una noticia en la sección principal del inicio:
- Editar la entrada
- En la barra lateral → **Visibilidad** → marcar **"Fijar en la página de inicio"** (Sticky)

### Fotorreportajes
- Crea una categoría llamada `fotorreportajes` (slug: `fotorreportajes`)
- O configura el slug en Personalizador → Más leídas/últimas

### Widgets
En **Apariencia → Widgets** puedes configurar:
- **Sidebar de Noticias**: Aparece junto a posts y páginas
- **Footer Área 1, 2, 3**: Aparecen en el pie de página

---

## Estructura de archivos

```
tiempo21-radiovictoria/
├── style.css                    # Estilos del tema
├── functions.php                # Funciones y configuración
├── header.php                   # Cabecera del sitio
├── footer.php                   # Pie de página
├── front-page.php               # Página de inicio
├── index.php                    # Archivo de blog / fallback
├── single.php                   # Entrada individual
├── page.php                     # Página estática
├── comments.php                 # Caja de comentarios
├── sidebar.php                  # Barra lateral
├── search.php                   # Resultados de búsqueda
├── author.php                   # Página de autor
├── 404.php                      # Página de error 404
├── template-parts/              # Componentes reutilizables
│   ├── post-card.php            # Card de noticias
│   ├── hero-main.php            # Noticia principal
│   ├── side-news-item.php       # Items de lista lateral
│   ├── cat1-item.php            # Item de categoría grande
│   ├── cat-mini-item.php        # Item de categoría pequeña
│   ├── photo-report-card.php    # Card de fotorreportaje
│   └── video-card.php          # Card de video
├── inc/                         # Funcionalidad adicional
│   ├── security.php             # Seguridad
│   ├── seo.php                  # SEO
│   ├── share-buttons.php        # Botones compartir
│   ├── breadcrumb/              # Breadcrumbs
│   ├── sitemap-generator/      # Generador sitemap
│   └── view-counter.php         # Contador de visitas
└── assets/
    ├── js/
    │   └── main.js              # JavaScript del tema
    └── fonts/
        ├── open-sans.css        # Open Sans
        └── fontawesome/          # Font Awesome
```

---

## Características

- ✅ Tema de noticias responsive
- ✅ Imagen destacada con verificación de archivo físico
- ✅ Placeholder automático cuando no hay imagen
- ✅ Audio player para radio en vivo
- ✅ Contador de visitas por post
- ✅ Sitemap XML automático
- ✅ SEO optimizado (Open Graph, Twitter Cards)
- ✅ Seguridad reforzada
- ✅ Código modular con template-parts

---

## Colores principales
- **Verde primario:** `#006622`
- **Azul secundario:** `#000097`
- **Acento/dorado:** `#e8b800`

---

## Soporte
Tema desarrollado para Tiempo21 - Radio Victoria, Las Tunas, Cuba.
