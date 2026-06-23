export function extractResourceId(resource) {
  if (resource == null) {
    return null;
  }

  if (typeof resource === "number") {
    return Number.isInteger(resource) && resource > 0 ? resource : null;
  }

  if (typeof resource === "string") {
    const parts = resource.split("/").filter(Boolean);
    const lastPart = parts.at(-1);
    const numericId = Number(lastPart);
    return Number.isInteger(numericId) && numericId > 0 ? numericId : null;
  }

  if (typeof resource === "object") {
    if (resource.id != null) {
      return extractResourceId(resource.id);
    }

    if (typeof resource["@id"] === "string") {
      return extractResourceId(resource["@id"]);
    }
  }

  return null;
}

export function resolveItemPath(item, endpoint = "") {
  const resourceId = extractResourceId(item);

  if (resourceId && endpoint) {
    return `${endpoint}/${resourceId}`;
  }

  if (typeof item?.["@id"] === "string" && item["@id"]) {
    const iri = item["@id"];

    if (iri.includes("/.well-known/genid/")) {
      return endpoint;
    }

    return iri.replace(/^\/api/, "") || endpoint;
  }

  return endpoint;
}

export function toApiIri(resourceName, resource) {
  const resourceId = extractResourceId(resource);
  return resourceId ? `/api/${resourceName}/${resourceId}` : "";
}
