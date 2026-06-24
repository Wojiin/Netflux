export function getDefaultRouteForUser(user) {
  const roles = Array.isArray(user?.roles) ? user.roles : [];

  return roles.includes("ROLE_ADMIN") ? "/admin" : "/movies";
}

export function getPostAuthRedirect(user, redirect) {
  return typeof redirect === "string" && redirect.trim()
    ? redirect
    : getDefaultRouteForUser(user);
}
