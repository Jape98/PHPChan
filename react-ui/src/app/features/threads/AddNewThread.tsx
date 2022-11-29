import { ErrorMessage, Form, Formik } from "formik";
import { observer } from "mobx-react-lite";
import { Button, Label, TextArea } from "semantic-ui-react";
import TextInput from "../../common/TextInput";
import { useStore } from "../../stores/store";


export default observer(function ThreadAdd() {
    const {threadStore} = useStore();
    const { userStore: {user, logout, isLoggedIn} } = useStore()
    return(
        
        <Formik
            initialValues={{threadContect: '', error: null}}
            onSubmit={(values, {setErrors}) => threadStore.addNewThread(values).catch(error => setErrors({error: "Error sending data"}))}
        >
            {({handleSubmit, isSubmitting, errors}) => (
                <Form className="ui form" onSubmit={handleSubmit} autoComplete="off">
                    <TextArea name="threadContext" placeholder="Write thread here" />
                    <ErrorMessage
                        name="error"
                        render={() => <Label style={{marginBottom: 10}} basic color="red" content={errors.error}/>}
                    />
                    <Button positive content="Add new thread" type="submit" loading={isSubmitting} fluid />
                </Form>
            )}
        </Formik>      
    )
})