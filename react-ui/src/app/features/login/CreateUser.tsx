import { ErrorMessage, Form, Formik } from "formik";
import { observer } from "mobx-react-lite";
import { Button, Label } from "semantic-ui-react";
import TextInput from "../../common/TextInput";
import { useStore } from "../../stores/store";


export default observer(function CreateUser() {
    const {userStore} = useStore();

    return( 
        <Formik
            initialValues={{username: '', password: '', email: '',  error: null}}
            onSubmit={(values, {setErrors}) => userStore.createUser(values).catch(error => setErrors({error: "Can't create user"}))}
        >
            {({handleSubmit, isSubmitting, errors}) => (
                <Form className="ui form" onSubmit={handleSubmit} autoComplete="off">
                    <TextInput name="username" placeholder="Username" />
                    <TextInput name="email" placeholder="Email" />
                    <TextInput name="password" placeholder="Password" type="password" />
                    <ErrorMessage
                        name="error"
                        render={() => <Label style={{marginBottom: 10}} basic color="red" content={errors.error}/>}
                    />
                    <Button positive content="Login" type="submit" loading={isSubmitting} fluid />
                </Form>
            )}
        </Formik>
    )
})