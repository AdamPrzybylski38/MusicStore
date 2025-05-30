import sys
import lmstudio as lms
import json

model = lms.llm("llama-3.2-3b-instruct")

system_prompt = {
    "role": "system",
    "content": "Jesteś asystentem AI sklepu muzycznego. Odpowiadasz uprzejmie, jasno i profesjonalnie. Udzielasz informacji zgodnie z intencją użytkownika."
    "Nie udzielasz informacji, które mogą być nieodpowiednie lub niebezpieczne. Nie udzielasz informacji o sobie ani o tym, jak działasz ani o tym, jak zostałeś stworzony. "
    "Nie udzielasz informacji o innych osobach ani o ich prywatnych sprawach. Nie udzielasz informacji, które mogą być niezgodne z prawem lub zasadami etyki. Nie udzielasz informacji, które mogą być obraźliwe lub szkodliwe dla innych osób. "
    "Nie udzielasz informacji, które mogą być nieodpowiednie dla dzieci lub młodzieży. Nie używaj formatowania tekstu, takiego jak pogrubienie, kursywa ani podkreślenie i znaczniki html. "
}

if len(sys.argv) > 2:
    user_query = sys.argv[1]
    history_json = sys.argv[2]

    try:
        history = json.loads(history_json)
    except json.JSONDecodeError:
        history = []

    messages = [system_prompt]

    for item in history:
        messages.append({"role": "user", "content": item["prompt"]})
        messages.append({"role": "assistant", "content": item["completion"]})

    messages.append({"role": "user", "content": user_query})

    result = model.respond({"messages": messages})
    print(result)

elif len(sys.argv) > 1:
    user_query = sys.argv[1]
    messages = [
        system_prompt,
        {"role": "user", "content": user_query}
    ]
    result = model.respond({"messages": messages})
    print(result)
else:
    print("Brak zapytania użytkownika.")