export const CONTENT_TYPES = [
  {
    value: "film",
    label: "Film",
    description: "Long-metrage de fiction ou d'animation.",
  },
  {
    value: "serie",
    label: "Serie",
    description: "Contenu episodique diffuse en plusieurs chapitres.",
  },
  {
    value: "documentaire",
    label: "Documentaire",
    description: "Programme de non-fiction base sur des faits reels.",
  },
  {
    value: "piece_de_theatre",
    label: "Piece de theatre",
    description: "Captation ou adaptation d'une oeuvre scenique.",
  },
];

export const ALL_CONTENT_TYPES_OPTION = {
  label: "Tous les types",
  value: "",
};

export function getContentTypeMeta(contentType) {
  return CONTENT_TYPES.find((type) => type.value === contentType) || null;
}

export function getContentTypeLabel(contentType, fallback = "Type inconnu") {
  return getContentTypeMeta(contentType)?.label || contentType || fallback;
}
