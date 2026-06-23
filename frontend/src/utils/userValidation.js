export function isValidEmail(value) {
  return typeof value === "string" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

export function isStrongEnoughPassword(value) {
  return typeof value === "string" && value.length >= 8;
}

export function validateUserCredentials({ email, plainPassword, confirmPassword, passwordOptional = false }) {
  if (!isValidEmail(email)) {
    return "Saisis une adresse email valide.";
  }

  if (!passwordOptional || plainPassword) {
    if (!isStrongEnoughPassword(plainPassword)) {
      return "Le mot de passe doit contenir au moins 8 caracteres.";
    }
  }

  if (typeof confirmPassword === "string" && plainPassword !== confirmPassword) {
    return "Les mots de passe ne correspondent pas.";
  }

  return "";
}
