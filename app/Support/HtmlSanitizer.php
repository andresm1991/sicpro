<?php

namespace App\Support;

/**
 * Sanitizador allowlist para el HTML de la agenda (S5).
 *
 * Sin dependencias: camina el DOM con DOMDocument/ext-dom y solo conserva
 * las etiquetas de la allowlist. No hay paquete purificador instalado y no
 * se debe agregar ninguno (composer/npm).
 *
 * Reglas:
 * - Etiquetas conservadas: p, br, strong, b, em, i, u, s, ul, ol, li,
 *   a[href], h1-h6, blockquote, table, thead, tbody, tr, td, th.
 * - Cualquier otro elemento se elimina pero se conserva su contenido de
 *   texto (unwrap), excepto <script> y <style>, cuyo contenido se elimina
 *   por completo (incluidos sus hijos).
 * - Se eliminan TODOS los atributos excepto href en <a>. Los href con
 *   esquema javascript:, data: o vbscript: se rechazan (se quita el
 *   atributo pero se conserva el texto del enlace).
 * - Decision documentada sobre imagenes: las <img> NO estan en la
 *   allowlist y se eliminan por completo. El base64 que Summernote incrusta
 *   al pegar imagenes es un peligro de formato de datos (bloat en la BD,
 *   TEXT gigante, listados pesados), asi que para la agenda las imagenes
 *   se descartan en lugar de conservarse.
 * - Un HTML sin contenido de texto (p. ej. el "<p><br></p>" que Summernote
 *   envia cuando el editor esta vacio) se normaliza a '' para no guardar
 *   basura en la BD.
 * - Solo se sanitiza al escribir (controlador). Nunca al leer.
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'ul', 'ol', 'li', 'a',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote',
        'table', 'thead', 'tbody', 'tr', 'td', 'th',
    ];

    private const DROP_ENTIRELY = ['script', 'style'];

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        if (trim($html) === '') {
            return $html;
        }

        // Sin texto real no hay nada que guardar (editor vacio de Summernote).
        if (trim(strip_tags($html)) === '') {
            return '';
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $wrapper = $doc->getElementsByTagName('div')->item(0);

        if ($wrapper === null) {
            return '';
        }

        self::sanitizeChildren($wrapper);

        $out = '';

        foreach ($wrapper->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function sanitizeChildren(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);

                continue;
            }

            if (!($child instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_ENTIRELY, true)) {
                $node->removeChild($child);

                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Se limpia por dentro primero para que un <script> anidado
                // en una etiqueta no permitida tambien pierda su contenido.
                self::sanitizeChildren($child);

                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }

                $node->removeChild($child);

                continue;
            }

            self::sanitizeChildren($child);
            self::cleanAttributes($child, $tag);
        }
    }

    private static function cleanAttributes(\DOMElement $element, string $tag): void
    {
        $href = null;

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $candidate = trim($element->getAttribute('href'));

            if ($candidate !== '' && self::isSafeHref($candidate)) {
                $href = $candidate;
            }
        }

        while ($element->attributes->length > 0) {
            $element->removeAttribute($element->attributes->item(0)->name);
        }

        if ($href !== null) {
            $element->setAttribute('href', $href);
        }
    }

    private static function isSafeHref(string $href): bool
    {
        // DOMDocument ya decodifico entidades (&#106;avascript: llega aqui
        // como javascript:), asi que basta con mirar el esquema inicial,
        // ignorando espacios y caracteres de control previos.
        $head = strtolower(preg_replace('/^[\x00-\x20]+/', '', $href));

        foreach (['javascript:', 'data:', 'vbscript:'] as $scheme) {
            if (str_starts_with($head, $scheme)) {
                return false;
            }
        }

        return true;
    }
}
