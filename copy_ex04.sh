#!/bin/bash

# Définir les dossiers cibles
targets=("ex05" "ex06" "ex07")

for target in "${targets[@]}"; do
    echo "🚀 Copie vers $target..."

    # Supprimer les anciens fichiers si existants
    rm -rf "$target/src" "$target/templates"

    # Copier src et templates
    cp -r ex04/src "$target/src"
    cp -r ex04/templates "$target/templates"

    # Copier les fichiers de configuration
    cp ex04/config/services.yaml "$target/config/services.yaml"
    cp ex04/config/packages/security.yaml "$target/config/packages/security.yaml"

    echo "✅ Terminé pour $target"
done

echo "✍️ Pense à changer tous les 'e04' en 'e05', 'e06', etc. dans les fichiers copiés !"

