#!/usr/bin/env bash
set -euo pipefail

BACK_OUT="back_code.txt"
FRONT_OUT="front_code.txt"

# Nettoyage
: > "$BACK_OUT"
: > "$FRONT_OUT"

# Fonction d’affichage avec arborescence
append_file() {
  local filepath="$1"
  local out="$2"

  if [ -f "$filepath" ]; then
    {
      echo ""
      echo "────────────────────────────────────────────"
      echo "📂 $(dirname "$filepath")"
      echo "└── 📄 $(basename "$filepath")"
      echo "────────────────────────────────────────────"
      cat "$filepath"
      echo -e "\n"
    } >> "$out"
  fi
}

echo "➡ Génération de $BACK_OUT..."

# Tout src/**
if [ -d "src" ]; then
  while IFS= read -r -d '' f; do
    append_file "$f" "$BACK_OUT"
  done < <(find src -type f -print0 | sort -z)
fi

# Configs spécifiques
append_file "config/packages/security.yaml" "$BACK_OUT"
append_file "config/routes.yaml" "$BACK_OUT"

echo "✅ $BACK_OUT généré."

echo "➡ Génération de $FRONT_OUT..."

# Tout templates/**
if [ -d "templates" ]; then
  while IFS= read -r -d '' f; do
    append_file "$f" "$FRONT_OUT"
  done < <(find templates -type f -print0 | sort -z)
fi

# CSS
append_file "public/.style.css" "$FRONT_OUT"
append_file "public/style.css" "$FRONT_OUT"

echo "✅ $FRONT_OUT généré."
