for i in ex01 ex02 ex03 ex04 ex05 ex06; do
  mkdir -p "$i/public"
  mkdir -p "$i/templates"
  cp ex07/public/style.css "$i/public/style.css"
  cp ex07/templates/base.html.twig "$i/templates/base.html.twig"
done

